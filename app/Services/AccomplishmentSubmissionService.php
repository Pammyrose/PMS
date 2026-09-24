<?php

namespace App\Services;

use App\Models\AccomplishmentSubmission;
use App\Models\FinancialAccomplishment;
use App\Models\Office;
use App\Models\PhysicalAccomplishment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccomplishmentSubmissionService
{
    private const PERIODS = [
        'jan', 'feb', 'mar', 'q1',
        'apr', 'may', 'jun', 'q2',
        'jul', 'aug', 'sep', 'q3',
        'oct', 'nov', 'dec', 'q4',
        'annual_total',
    ];

    private const MONTH_PERIODS = [
        'jan', 'feb', 'mar', 'apr', 'may', 'jun',
        'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
    ];

    public function queueLockedChange(
        User $user,
        string $submissionType,
        string $sector,
        array $entry,
        array $changedPeriods,
        string $reason
    ): AccomplishmentSubmission {
        abort_unless(in_array($submissionType, ['physical', 'financial'], true), 422, 'Unsupported submission type.');

        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages([
                'entries' => 'A reason is required when requesting a change to a locked month.',
            ]);
        }

        $userOfficeId = (int) ($user->office_id ?? 0);
        abort_if($userOfficeId <= 0, 422, 'Your account must have an assigned office.');
        abort_if((int) ($entry['office_id'] ?? 0) !== $userOfficeId, 403, 'You may request changes only for your assigned office.');

        $penroOfficeId = Office::penroOfficeIdFor($userOfficeId);
        if ($penroOfficeId === null) {
            throw ValidationException::withMessages([
                'office_id' => 'The assigned office is not configured for accomplishment change requests.',
            ]);
        }

        $programId = (int) $entry['program_id'];
        $rowId = (int) ($entry['row_id'] ?? $programId);
        $indicatorId = (int) $entry['indicator_id'];
        $year = (int) $entry['year'];
        $changedPeriods = collect($changedPeriods)
            ->filter(fn ($period) => in_array($period, self::MONTH_PERIODS, true))
            ->unique()
            ->values()
            ->all();

        abort_if($changedPeriods === [], 422, 'No locked month was changed.');

        $payload = [
            'car_totals' => $entry['car_totals'] ?? [],
            'group_totals' => $entry['group_totals'] ?? [],
            '_changed_periods' => $changedPeriods,
        ];

        foreach (self::PERIODS as $period) {
            $payload[$period] = $entry[$period] ?? 0;
        }

        if ($submissionType === 'physical') {
            $payload['remarks'] = filled($entry['remarks'] ?? null)
                ? trim((string) $entry['remarks'])
                : null;
        }

        return AccomplishmentSubmission::query()->updateOrCreate([
            'submission_type' => $submissionType,
            'sector' => $sector,
            'user_id' => $user->id,
            'office_id' => $userOfficeId,
            'row_id' => $rowId,
            'indicator_id' => $indicatorId,
            'year' => $year,
            'status' => 'pending',
        ], [
            'penro_office_id' => $penroOfficeId,
            'program_id' => $programId,
            'payload' => $payload,
            'request_reason' => $reason,
            'reviewed_by' => null,
            'review_notes' => null,
            'reviewed_at' => null,
        ]);
    }

    public function queue(User $user, string $submissionType, string $sector, array $entries): int
    {
        abort_unless(in_array($submissionType, ['physical', 'financial'], true), 422, 'Unsupported submission type.');

        $userOfficeId = (int) ($user->office_id ?? 0);

        if ($userOfficeId <= 0) {
            throw ValidationException::withMessages([
                'office_id' => 'Your account must have an assigned office before submitting accomplishments.',
            ]);
        }

        $penroOfficeId = Office::penroOfficeIdFor($userOfficeId);

        if ($penroOfficeId === null) {
            throw ValidationException::withMessages([
                'office_id' => 'No PENRO reviewer is configured for your assigned office.',
            ]);
        }

        return DB::transaction(function () use ($entries, $penroOfficeId, $sector, $submissionType, $user, $userOfficeId) {
            $queued = 0;

            foreach ($entries as $entry) {
                if ((int) ($entry['office_id'] ?? 0) !== $userOfficeId) {
                    abort(403, 'You may submit accomplishments only for your assigned office.');
                }

                $programId = (int) $entry['program_id'];
                $rowId = (int) ($entry['row_id'] ?? $programId);
                $indicatorId = (int) $entry['indicator_id'];
                $year = (int) $entry['year'];

                $changedPeriods = collect($entry['changed_periods'] ?? [])
                    ->filter(fn ($period) => in_array($period, self::PERIODS, true))
                    ->unique()
                    ->values()
                    ->all();

                if ($changedPeriods === []) {
                    $changedPeriods = $this->inferChangedMonths(
                        $submissionType,
                        $sector,
                        $userOfficeId,
                        $rowId,
                        $indicatorId,
                        $year,
                        $entry
                    );
                }

                $payload = [
                    'car_totals' => $entry['car_totals'] ?? [],
                    'group_totals' => $entry['group_totals'] ?? [],
                    '_changed_periods' => $changedPeriods,
                ];

                foreach (self::PERIODS as $period) {
                    $payload[$period] = $entry[$period] ?? 0;
                }

                if ($submissionType === 'physical') {
                    $payload['remarks'] = filled($entry['remarks'] ?? null)
                        ? trim((string) $entry['remarks'])
                        : null;
                }

                AccomplishmentSubmission::query()->updateOrCreate([
                    'submission_type' => $submissionType,
                    'sector' => $sector,
                    'user_id' => $user->id,
                    'office_id' => $userOfficeId,
                    'row_id' => $rowId,
                    'indicator_id' => $indicatorId,
                    'year' => $year,
                    'status' => 'pending',
                ], [
                    'penro_office_id' => $penroOfficeId,
                    'program_id' => $programId,
                    'payload' => $payload,
                    'reviewed_by' => null,
                    'review_notes' => null,
                    'reviewed_at' => null,
                ]);

                $queued++;
            }

            return $queued;
        });
    }

    private function inferChangedMonths(
        string $submissionType,
        string $sector,
        int $officeId,
        int $rowId,
        int $indicatorId,
        int $year,
        array $entry
    ): array {
        $modelClass = $submissionType === 'financial'
            ? FinancialAccomplishment::class
            : PhysicalAccomplishment::class;

        $existing = $modelClass::query()->where([
            'sector' => $sector,
            'office_id' => $officeId,
            'row_id' => $rowId,
            'indicator_id' => $indicatorId,
            'year' => $year,
        ])->first();

        return collect(self::MONTH_PERIODS)
            ->filter(function (string $period) use ($entry, $existing) {
                $submittedValue = (float) ($entry[$period] ?? 0);
                $existingValue = (float) ($existing?->{$period} ?? 0);

                return $submittedValue !== $existingValue;
            })
            ->values()
            ->all();
    }

    public function approve(AccomplishmentSubmission $submission, User $reviewer): void
    {
        abort_unless($reviewer->isAdmin() || $reviewer->isRegionalOffice(), 403);

        DB::transaction(function () use ($reviewer, $submission) {
            $lockedSubmission = AccomplishmentSubmission::query()
                ->lockForUpdate()
                ->findOrFail($submission->id);

            abort_unless($lockedSubmission->status === 'pending', 409, 'This submission has already been reviewed.');

            $modelClass = $lockedSubmission->submission_type === 'financial'
                ? FinancialAccomplishment::class
                : PhysicalAccomplishment::class;

            $record = $modelClass::firstOrNew([
                'sector' => $lockedSubmission->sector,
                'year' => $lockedSubmission->year,
                'office_id' => $lockedSubmission->office_id,
                'row_id' => $lockedSubmission->row_id,
                'indicator_id' => $lockedSubmission->indicator_id,
            ]);

            $payload = (array) $lockedSubmission->payload;
            $values = [
                'user_id' => $lockedSubmission->user_id,
                'program_id' => $lockedSubmission->program_id,
            ];

            $changedPeriods = collect($payload['_changed_periods'] ?? [])
                ->filter(fn ($period) => in_array($period, self::MONTH_PERIODS, true))
                ->unique()
                ->values()
                ->all();

            $values['car_totals'] = $this->mergeLockedTotals(
                (array) ($record->car_totals ?? []),
                (array) ($payload['car_totals'] ?? []),
                $changedPeriods
            );
            $values['group_totals'] = collect((array) ($payload['group_totals'] ?? []))
                ->reduce(function (array $totals, $requestedGroup, $groupKey) use ($changedPeriods) {
                    $totals[$groupKey] = $this->mergeLockedTotals(
                        (array) ($totals[$groupKey] ?? []),
                        (array) $requestedGroup,
                        $changedPeriods
                    );

                    return $totals;
                }, (array) ($record->group_totals ?? []));

            foreach ($changedPeriods as $period) {
                $values[$period] = $payload[$period] ?? 0;
            }

            if ($lockedSubmission->submission_type === 'physical') {
                $values['remarks'] = $payload['remarks'] ?? null;
                $values['imported_from'] = null;
            }

            $record->fill($values);
            $record->fill($this->calculatedTotals(
                $record,
                $lockedSubmission->submission_type,
                (int) $lockedSubmission->indicator_id
            ));
            $record->save();

            $lockedSubmission->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'review_notes' => null,
                'reviewed_at' => now(),
                'user_read_at' => null,
            ]);
        });
    }

    private function calculatedTotals(Model $record, string $submissionType, int $indicatorId): array
    {
        $months = collect(self::MONTH_PERIODS)
            ->map(fn (string $period) => (float) ($record->{$period} ?? 0))
            ->values()
            ->all();

        $indicatorType = $submissionType === 'financial'
            ? 'cumulative'
            : strtolower((string) DB::table('indicators')
                ->leftJoin('indicator_types', 'indicator_types.id', '=', 'indicators.indicator_type_id')
                ->where('indicators.id', $indicatorId)
                ->value('indicator_types.name'));

        $quarterValue = fn (array $values): float => $indicatorType === 'non-cumulative'
            ? max([0, ...$values])
            : array_sum($values);
        $quarters = [
            $quarterValue(array_slice($months, 0, 3)),
            $quarterValue(array_slice($months, 3, 3)),
            $quarterValue(array_slice($months, 6, 3)),
            $quarterValue(array_slice($months, 9, 3)),
        ];

        return [
            'q1' => $quarters[0],
            'q2' => $quarters[1],
            'q3' => $quarters[2],
            'q4' => $quarters[3],
            'annual_total' => $indicatorType === 'semi-cumulative'
                ? max([0, ...$quarters])
                : array_sum($quarters),
        ];
    }

    private function mergeLockedTotals(array $stored, array $requested, array $changedPeriods): array
    {
        foreach ($changedPeriods as $period) {
            if (array_key_exists($period, $requested)) {
                $stored[$period] = $requested[$period];
            }
        }

        return $stored;
    }
}
