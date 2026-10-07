<?php

namespace App\Support;

final class NonCumulativeAnnualTotal
{
    /** Most frequent value, used for both monthly-to-quarter and quarterly-to-annual totals.
     * @param array<int, float|int> $quarters
     */
    public static function calculate(array $quarters): float
    {
        $counts = [];
        $result = 0.0;
        $highestCount = 0;
        foreach ($quarters as $value) {
            $key = (string) $value;
            $count = ($counts[$key] ?? 0) + 1;
            $counts[$key] = $count;
            if ($count > $highestCount || ($count === $highestCount && $value > $result)) {
                $result = (float) $value;
                $highestCount = $count;
            }
        }

        return $result;
    }
}
