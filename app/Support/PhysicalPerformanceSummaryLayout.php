<?php

namespace App\Support;

final class PhysicalPerformanceSummaryLayout
{
    public static function title(): string
    {
        return 'Physical Performance';
    }

    /**
     * This is the single physical-summary definition used by both the UI and
     * the generated Excel workbook.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function sections(): array
    {
        return [
            [
                'key' => 'physical-target',
                'label' => 'Target',
                'inputSection' => 'target',
                'periods' => [
                    ['key' => 'annual', 'label' => 'Annual'],
                    ['key' => 'quarter', 'label' => 'This Quarter', 'excelLabel' => "This\nQuarter"],
                    ['key' => 'to-date', 'label' => 'To Date'],
                ],
            ],
            [
                'key' => 'physical-accomplishment',
                'label' => 'Accomp',
                'inputSection' => 'accomp',
                'periods' => [
                    ['key' => 'quarter', 'label' => 'This Quarter', 'excelLabel' => "This\nQuarter"],
                    ['key' => 'to-date', 'label' => 'To Date'],
                ],
            ],
            [
                'key' => 'physical-percentage',
                'label' => '%Accomp',
                'isPercentage' => true,
                'periods' => [
                    [
                        'key' => 'to-date-percent',
                        'label' => 'To Date%',
                        'excelLabel' => "To Date\n%",
                        'numeratorSection' => 'physical-accomplishment',
                        'numeratorPeriod' => 'to-date',
                        'denominatorSection' => 'physical-target',
                        'denominatorPeriod' => 'to-date',
                        'targetPeriod' => 'to-date',
                    ],
                    [
                        'key' => 'annual-percent',
                        'label' => 'Annual',
                        'numeratorSection' => 'physical-accomplishment',
                        'numeratorPeriod' => 'to-date',
                        'denominatorSection' => 'physical-target',
                        'denominatorPeriod' => 'annual',
                        'targetPeriod' => 'annual',
                    ],
                ],
            ],
        ];
    }

    public static function columnCount(): int
    {
        return array_sum(array_map(
            static fn (array $section): int => count($section['periods'] ?? []),
            self::sections()
        ));
    }
}
