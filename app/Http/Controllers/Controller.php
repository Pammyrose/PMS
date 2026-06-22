<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

abstract class Controller
{
    protected function roleView(string $view): string
    {
        $user = auth()->user();

        if ($user?->isRegionalOffice()) {
            return 'regional.' . $view;
        }

        if ($user?->isUser()) {
            return 'users.' . $view;
        }

        return 'admin.' . $view;
    }

    protected function officeIdForPhysicalPage(Request $request, int $defaultOfficeId = 1): int
    {
        $user = auth()->user();

        if ($user && ! $user->isAdmin() && ! $user->isRegionalOffice() && (int) ($user->office_id ?? 0) > 0) {
            return (int) $user->office_id;
        }

        return (int) $request->query('office_id', $defaultOfficeId);
    }

    protected function filterIndicatorsForOffice(Collection $indicators, int $officeId): Collection
    {
        if (! $this->shouldScopeToUserOffice()) {
            return $indicators;
        }

        return $indicators->map(function (Collection $programIndicators) use ($officeId) {
            return $programIndicators
                ->filter(function ($indicator) use ($officeId) {
                    return collect($indicator->office_id ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->contains($officeId);
                })
                ->map(function ($indicator) use ($officeId) {
                    $indicatorClone = clone $indicator;
                    $indicatorClone->office_id = [$officeId];

                    return $indicatorClone;
                })
                ->values();
        });
    }

    protected function filterProgramRowsForOffice(Collection $programs, Collection $indicators, int $officeId): Collection
    {
        if (! $this->shouldScopeToUserOffice()) {
            return $programs;
        }

        $allowedRowIds = $indicators
            ->filter(fn (Collection $programIndicators) => $programIndicators->isNotEmpty())
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->flip();

        if ($allowedRowIds->isEmpty()) {
            return collect();
        }

        return $programs
            ->filter(function ($program) use ($allowedRowIds) {
                $rowIds = [
                    (int) ($program->id ?? 0),
                    (int) ($program->row_id ?? 0),
                    (int) ($program->program_row_id ?? 0),
                    (int) ($program->project_row_id ?? 0),
                    (int) ($program->main_activity_row_id ?? 0),
                    (int) ($program->sub_activity_row_id ?? 0),
                    (int) ($program->sub_sub_activity_row_id ?? 0),
                    (int) ($program->sub_sub_sub_activity_row_id ?? 0),
                    (int) ($program->level_7_row_id ?? 0),
                    (int) ($program->level_8_row_id ?? 0),
                    (int) ($program->level_9_row_id ?? 0),
                ];

                foreach ($rowIds as $rowId) {
                    if ($rowId > 0 && $allowedRowIds->has($rowId)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }

    protected function filterSectionDataForOffice(array $sectionData, int $officeId): array
    {
        if (! $this->shouldScopeToUserOffice()) {
            return $sectionData;
        }

        $officeKey = (string) $officeId;

        foreach ($sectionData as $programId => $indicators) {
            foreach ($indicators as $indicatorId => $officeRows) {
                $officeRows = is_array($officeRows) ? $officeRows : [];

                if (array_key_exists($officeKey, $officeRows)) {
                    $sectionData[$programId][$indicatorId] = [
                        $officeKey => $officeRows[$officeKey],
                    ];
                    continue;
                }

                unset($sectionData[$programId][$indicatorId]);
            }

            if (empty($sectionData[$programId])) {
                unset($sectionData[$programId]);
            }
        }

        return $sectionData;
    }

    protected function shouldScopeToUserOffice(): bool
    {
        $user = auth()->user();

        return $user !== null
            && ! $user->isAdmin()
            && ! $user->isRegionalOffice()
            && (int) ($user->office_id ?? 0) > 0;
    }
}
