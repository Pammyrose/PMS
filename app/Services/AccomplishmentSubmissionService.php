<?php

namespace App\Services;

use App\Models\AccomplishmentSubmission;
use App\Models\FinancialAccomplishment;
use App\Models\Office;
use App\Models\PhysicalAccomplishment;
use App\Models\User;
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
        abort_unless($reviewer->isPenro(), 403);
        abort_unless(
            (int) $submission->penro_office_id === (int) $reviewer->office_id,
            403,
            'This submission belongs to another PENRO.'
        );

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
                'car_totals' => $payload['car_totals'] ?? [],
                'group_totals' => $payload['group_totals'] ?? [],
            ];

            foreach (self::PERIODS as $period) {
                $values[$period] = $payload[$period] ?? 0;
            }

            if ($lockedSubmission->submission_type === 'physical') {
                $values['remarks'] = $payload['remarks'] ?? null;
                $values['imported_from'] = null;
            }

            $record->fill($values)->save();

            $lockedSubmission->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->id,
                'review_notes' => null,
                'reviewed_at' => now(),
                'user_read_at' => null,
            ]);
        });
    }
}
