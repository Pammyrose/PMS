<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
class AccomplishmentPeriodLock
{
    private const MONTHS = [
        'jan', 'feb', 'mar', 'apr', 'may', 'jun',
        'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
    ];

    public function changedLockedPeriods(array $entry, ?Model $existing): array
    {
        $year = (int) ($entry['year'] ?? 0);
        $now = now('Asia/Manila');
        $lockedMonthCount = $year < $now->year
            ? 12
            : ($year === $now->year ? $now->month - 1 : 0);

        return collect(array_slice(self::MONTHS, 0, $lockedMonthCount))
            ->filter(function (string $period) use ($entry, $existing) {
                $submitted = (float) ($entry[$period] ?? 0);
                $stored = (float) ($existing?->{$period} ?? 0);

                return abs($submitted - $stored) > 0.0000001;
            })
            ->values()
            ->all();
    }
}
