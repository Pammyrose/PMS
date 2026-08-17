<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use ZipArchive;

class OfficialWfpTemplateWriter
{
    private const PERIODS = [
        'jan', 'feb', 'mar', 'q1',
        'apr', 'may', 'jun', 'q2',
        'jul', 'aug', 'sep', 'q3',
        'oct', 'nov', 'dec', 'q4',
        'annual_total',
    ];

    private const MONTH_KEYS = [
        'jan', 'feb', 'mar',
        'apr', 'may', 'jun',
        'jul', 'aug', 'sep',
        'oct', 'nov', 'dec',
    ];

    private const SUMMARY_COLUMNS = [
        'physical_target' => ['I', 'J', 'K'],
        'financial_target' => ['AB', 'AC', 'AD'],
        'physical_accomplishment' => ['AU', 'AV', 'AW'],
        'financial_accomplishment' => ['BN', 'BO', 'BP'],
    ];

    private const OFFICE_GROUPS = [
        'ABRA' => ['ABRA', 'BANGUED', 'LAGANGILANG'],
        'APAYAO' => ['APAYAO', 'CALANASAN', 'CONNER'],
        'BENGUET' => ['BENGUET', 'BAGUIO', 'BUGUIAS'],
        'IFUGAO' => ['IFUGAO', 'ALFONSOLISTA', 'LAMUT'],
        'KALINGA' => ['KALINGA', 'PINUKPUK', 'TABUK'],
        'MTPROVINCE' => ['MTPROVINCE', 'PARACELIS', 'SABANGAN'],
    ];

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<int, string>|null  $allowedOfficeNames
     * @return array{matched_blocks:int, total_blocks:int, updated_rows:int}
     */
    public function write(
        string $templatePath,
        string $outputPath,
        string $sheetName,
        array $rows,
        array $blocks,
        ?DateTimeInterface $asOf = null,
        ?array $allowedOfficeNames = null
    ): array {
        $asOf ??= new DateTimeImmutable;
        if (! is_file($templatePath)) {
            throw new RuntimeException('The official WFP Excel template is missing.');
        }

        if (! copy($templatePath, $outputPath)) {
            throw new RuntimeException('Unable to prepare the official WFP Excel template.');
        }

        $templateRows = iterator_to_array((new SimpleXlsxReader)->rows($templatePath, $sheetName, false));
        $dataGroups = $this->dataGroups($rows);
        $usedGroups = [];
        $updates = [];
        $matchedBlocks = 0;
        $updatedRows = 0;
        $lastTemplateRow = empty($templateRows) ? 0 : max(array_keys($templateRows));
        $officeRowScope = $this->officeRowScope($templateRows, $allowedOfficeNames);

        foreach (array_values($blocks) as $blockIndex => $block) {
            $sourceRow = (int) ($block['row'] ?? 0);
            if ($sourceRow <= 0) {
                continue;
            }

            $nextRow = (int) ($blocks[$blockIndex + 1]['row'] ?? ($lastTemplateRow + 1));
            $groupIndex = $this->matchingDataGroup($block, $dataGroups, $usedGroups);
            if ($groupIndex === null) {
                continue;
            }

            $usedGroups[$groupIndex] = true;
            $matchedBlocks++;
            $group = $dataGroups[$groupIndex];
            $currentParent = null;

            for ($rowNumber = $sourceRow; $rowNumber < $nextRow; $rowNumber++) {
                $location = trim((string) ($templateRows[$rowNumber]['C'] ?? ''));
                if ($location === '') {
                    continue;
                }

                $normalizedLocation = $this->normalizeOffice($location);
                if (isset(self::OFFICE_GROUPS[$normalizedLocation])) {
                    $currentParent = $normalizedLocation;
                    $values = $this->sumOfficeGroup($group['offices'], self::OFFICE_GROUPS[$normalizedLocation]);
                } elseif ($normalizedLocation === 'PENRO' && $currentParent !== null) {
                    $values = $group['offices'][$currentParent] ?? [];
                } else {
                    $officeKey = $this->officeAlias($normalizedLocation);
                    $values = $group['offices'][$officeKey] ?? [];
                }

                if ($values === []) {
                    continue;
                }

                $this->collectCellUpdates(
                    $updates,
                    $rowNumber,
                    $values,
                    (string) ($group['indicator_type'] ?? 'cumulative'),
                    $asOf
                );
                $updatedRows++;
            }
        }

        $this->applyUpdates(
            $outputPath,
            $sheetName,
            $updates,
            $asOf,
            $officeRowScope['hidden'],
            $officeRowScope['clear_location']
        );

        return [
            'matched_blocks' => $matchedBlocks,
            'total_blocks' => count($blocks),
            'updated_rows' => $updatedRows,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array{pap:string,indicator:string,indicator_type:string,offices:array<string,array<string,mixed>>}>
     */
    private function dataGroups(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $pap = (string) ($row['pap'] ?? '');
            $indicator = (string) ($row['indicator'] ?? '');
            $key = $this->normalizeText($pap).'|'.$this->normalizeText($indicator);
            $groups[$key] ??= [
                'pap' => $pap,
                'indicator' => $indicator,
                'indicator_type' => (string) ($row['indicator_type'] ?? 'cumulative'),
                'offices' => [],
            ];
            $office = $this->normalizeOffice((string) ($row['office'] ?? ''));
            if ($office !== '') {
                $groups[$key]['offices'][$this->officeAlias($office)] = [
                    'physical_target' => $row['physical_target'] ?? [],
                    'financial_target' => $row['financial_target'] ?? [],
                    'physical_accomplishment' => $row['physical_accomplishment'] ?? [],
                    'financial_accomplishment' => $row['financial_accomplishment'] ?? [],
                ];
            }
        }

        return array_values($groups);
    }

    /**
     * @param  array<string, mixed>  $block
     * @param  array<int, array{pap:string,indicator:string,indicator_type:string,offices:array<string,array<string,mixed>>}>  $groups
     * @param  array<int, bool>  $usedGroups
     */
    private function matchingDataGroup(array $block, array $groups, array $usedGroups): ?int
    {
        $indicator = $this->normalizeText((string) ($block['indicator'] ?? ''));
        $hierarchy = collect($block['hierarchy'] ?? [])
            ->map(fn ($value) => $this->normalizeText((string) $value))
            ->filter()
            ->values()
            ->all();
        $titleParts = [
            $this->normalizeText((string) ($block['title'] ?? '')),
            $this->normalizeText((string) ($block['program'] ?? '')),
            $this->normalizeText((string) ($block['project'] ?? '')),
            ...$hierarchy,
        ];
        $titleParts = array_values(array_filter(
            $titleParts,
            fn (string $part): bool => $part !== '' && ! in_array($part, ['NA', 'NOTAPPLICABLE'], true)
        ));

        $bestIndex = null;
        $bestScore = -1;
        foreach ($groups as $index => $group) {
            $groupIndicator = $this->normalizeText($group['indicator']);
            if ($indicator === '' && $groupIndicator === '') {
                $score = 0;
            } elseif ($indicator !== '' && $groupIndicator !== '') {
                $score = $groupIndicator === $indicator
                    ? 1000
                    : ($this->containsEither($groupIndicator, $indicator) ? 500 : -1);
            } else {
                $score = -1;
            }
            if ($score < 0) {
                continue;
            }

            $pap = $this->normalizeText($group['pap']);
            foreach ($titleParts as $part) {
                if ($part !== '' && str_contains($pap, $part)) {
                    $score += $indicator === '' ? 100 : 25;
                }
            }

            if ($indicator === '' && $score === 0) {
                continue;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $index;
            }
        }

        return $bestIndex;
    }

    private function containsEither(string $left, string $right): bool
    {
        return str_contains($left, $right) || str_contains($right, $left);
    }

    /**
     * @param  array<string, array<string, mixed>>  $offices
     * @param  array<int, string>  $officeKeys
     * @return array<string, array<string, float>>
     */
    private function sumOfficeGroup(array $offices, array $officeKeys): array
    {
        $totals = [];
        foreach (array_keys(self::SUMMARY_COLUMNS) as $kind) {
            $hasKind = false;
            $kindTotals = array_fill_keys(self::PERIODS, 0.0);
            foreach ($officeKeys as $officeKey) {
                $periods = $offices[$officeKey][$kind] ?? [];
                if ($periods === []) {
                    continue;
                }
                $hasKind = true;
                foreach (self::PERIODS as $period) {
                    $kindTotals[$period] += is_numeric($periods[$period] ?? null)
                        ? (float) $periods[$period]
                        : 0.0;
                }
            }
            if ($hasKind) {
                $totals[$kind] = $kindTotals;
            }
        }

        return $totals;
    }

    /**
     * @param  array<string, float|int>  $updates
     * @param  array<string, array<string, mixed>>  $values
     */
    private function collectCellUpdates(
        array &$updates,
        int $rowNumber,
        array $values,
        string $indicatorType,
        DateTimeInterface $asOf
    ): void {
        foreach (self::SUMMARY_COLUMNS as $kind => $columns) {
            $periods = $values[$kind] ?? [];
            if (! is_array($periods) || $periods === []) {
                continue;
            }
            $type = str_starts_with($kind, 'financial_') ? 'cumulative' : $indicatorType;
            $summary = $this->summaryValues($periods, (int) $asOf->format('n'), $type);
            foreach ($columns as $index => $column) {
                $updates[$column.$rowNumber] = $summary[$index];
            }
        }
    }

    /**
     * @param  array<string, mixed>  $periods
     * @return array{0:float,1:float,2:float}
     */
    private function summaryValues(array $periods, int $month, string $indicatorType): array
    {
        $monthlyValues = array_map(
            fn (string $key): float => is_numeric($periods[$key] ?? null) ? (float) $periods[$key] : 0.0,
            self::MONTH_KEYS
        );
        $normalized = preg_replace('/[^a-z]+/', '', strtolower($indicatorType)) ?? '';
        $type = match (true) {
            str_starts_with($normalized, 'non') => 'non-cumulative',
            str_starts_with($normalized, 'semi') => 'semi-cumulative',
            default => 'cumulative',
        };
        $quarterValue = static fn (array $values): float => $type === 'non-cumulative'
            ? max([0.0, ...$values])
            : array_sum($values);
        $quarterValues = [];
        for ($start = 0; $start < 12; $start += 3) {
            $quarterValues[] = $quarterValue(array_slice($monthlyValues, $start, 3));
        }

        $annual = $type === 'semi-cumulative'
            ? max([0.0, ...$quarterValues])
            : array_sum($quarterValues);
        $month = max(1, min(12, $month));
        $quarter = $quarterValues[intdiv($month - 1, 3)];
        $monthsToDate = array_slice($monthlyValues, 0, $month);

        if ($type === 'cumulative') {
            $toDate = array_sum($monthsToDate);
        } else {
            $quarterValuesToDate = [];
            for ($start = 0; $start < count($monthsToDate); $start += 3) {
                $quarterValuesToDate[] = $quarterValue(array_slice($monthsToDate, $start, 3));
            }
            $toDate = $type === 'semi-cumulative'
                ? max([0.0, ...$quarterValuesToDate])
                : array_sum($quarterValuesToDate);
        }

        return [$annual, $quarter, $toDate];
    }

    /** @param array<string, float|int> $updates */
    private function applyUpdates(
        string $path,
        string $sheetName,
        array $updates,
        DateTimeInterface $asOf,
        array $hiddenOfficeRows = [],
        array $clearedOfficeRows = []
    ): void {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open the official WFP workbook.');
        }

        try {
            $sheetPath = $this->sheetPath($zip, $sheetName);
            $sheetXml = $zip->getFromName($sheetPath);
            if ($sheetXml === false) {
                throw new RuntimeException("Unable to read the {$sheetName} worksheet.");
            }
            $sheetXml = $this->transformSummaryLayout($sheetXml, $asOf);
            $sheetXml = $this->hideRows($sheetXml, $hiddenOfficeRows);
            foreach ($clearedOfficeRows as $rowNumber) {
                $sheetXml = $this->clearCell($sheetXml, 'C'.(int) $rowNumber);
            }

            $updatedXml = preg_replace_callback(
                '/<c\b([^>]*\br="([A-Z]+[0-9]+)"[^>]*)(?:\/>|>(?:<f\b[^>]*(?:\/>|>[^<]*<\/f>))?(?:<v(?:\/>|>[^<]*<\/v>))?<\/c>)/',
                function (array $matches) use ($updates): string {
                    $reference = $matches[2];
                    if (! array_key_exists($reference, $updates)) {
                        if ($this->isSummaryDataReference($reference)) {
                            $attributes = preg_replace('/\s+t="[^"]*"/', '', $matches[1]) ?? $matches[1];

                            return '<c'.$attributes.'><v>0</v></c>';
                        }

                        // The official workbook uses shared formula groups. Replacing only
                        // some cells in one of those groups leaves orphaned shared-formula
                        // references, which Excel treats as a damaged workbook. This export
                        // is a static report, so retain each cached value and remove formulas.
                        return preg_replace(
                            '/<f\b[^>]*(?:\/>|>[^<]*<\/f>)/',
                            '',
                            $matches[0]
                        ) ?? $matches[0];
                    }

                    $attributes = preg_replace('/\s+t="[^"]*"/', '', $matches[1]) ?? $matches[1];
                    $number = rtrim(rtrim(number_format((float) $updates[$reference], 4, '.', ''), '0'), '.');

                    return '<c'.$attributes.'><v>'.($number === '' || $number === '-0' ? '0' : $number).'</v></c>';
                },
                $sheetXml
            );
            if (! is_string($updatedXml)) {
                throw new RuntimeException(
                    "Unable to update the {$sheetName} worksheet: ".preg_last_error_msg()
                );
            }

            $zip->addFromString($sheetPath, $updatedXml);
            $this->removeCalculationChain($zip);
        } finally {
            $zip->close();
        }
    }

    private function isSummaryDataReference(string $reference): bool
    {
        if (! preg_match('/^([A-Z]+)([0-9]+)$/', $reference, $matches) || (int) $matches[2] < 10) {
            return false;
        }

        return in_array($matches[1], array_merge(...array_values(self::SUMMARY_COLUMNS)), true);
    }

    /**
     * Hide location rows outside a PENRO's assigned service area while retaining
     * the official workbook structure, formulas, formatting, and print layout.
     *
     * @param  array<int, array<string, mixed>>  $templateRows
     * @param  array<int, string>|null  $allowedOfficeNames
     * @return array{hidden:array<int, int>,clear_location:array<int, int>}
     */
    private function officeRowScope(array $templateRows, ?array $allowedOfficeNames): array
    {
        if ($allowedOfficeNames === null) {
            return ['hidden' => [], 'clear_location' => []];
        }

        $allowed = collect($allowedOfficeNames)
            ->map(fn (string $office): string => $this->officeAlias($this->normalizeOffice($office)))
            ->filter()
            ->flip();
        $knownOffices = collect(self::OFFICE_GROUPS)
            ->flatten()
            ->push('CAR', 'RO')
            ->flip();
        $currentParent = null;
        $hiddenRows = [];
        $clearedLocationRows = [];

        foreach ($templateRows as $rowNumber => $row) {
            $location = $this->normalizeOffice((string) ($row['C'] ?? ''));
            if ($location === '') {
                continue;
            }

            if (isset(self::OFFICE_GROUPS[$location])) {
                $currentParent = $location;
                $officeKey = $location;
            } elseif ($location === 'PENRO' && $currentParent !== null) {
                $officeKey = $currentParent;
            } else {
                $officeKey = $this->officeAlias($location);
                if (! $knownOffices->has($officeKey)) {
                    continue;
                }
            }

            if (! $allowed->has($officeKey)) {
                $hasPapOrIndicator = trim((string) ($row['A'] ?? '')) !== ''
                    || trim((string) ($row['B'] ?? '')) !== '';

                if ($hasPapOrIndicator) {
                    // The official form anchors merged P/A/P and indicator labels on
                    // some CAR rows. Keep that anchor visible, but remove its office.
                    $clearedLocationRows[] = (int) $rowNumber;
                } else {
                    $hiddenRows[] = (int) $rowNumber;
                }
            }
        }

        return [
            'hidden' => array_values(array_unique($hiddenRows)),
            'clear_location' => array_values(array_unique($clearedLocationRows)),
        ];
    }

    /** @param array<int, int> $rowNumbers */
    private function hideRows(string $xml, array $rowNumbers): string
    {
        if ($rowNumbers === []) {
            return $xml;
        }

        $hidden = array_fill_keys(array_map('strval', $rowNumbers), true);

        return preg_replace_callback('/<row\b([^>]*)>/', function (array $matches) use ($hidden): string {
            if (! preg_match('/\br="([0-9]+)"/', $matches[1], $rowMatch) || ! isset($hidden[$rowMatch[1]])) {
                return $matches[0];
            }

            $attributes = preg_replace('/\s+hidden="[^"]*"/', '', $matches[1]) ?? $matches[1];

            return '<row'.$attributes.' hidden="1">';
        }, $xml) ?? $xml;
    }

    private function transformSummaryLayout(string $xml, DateTimeInterface $asOf): string
    {
        $xml = $this->collapseMonthlyColumns($xml);
        $quarter = (int) ceil(((int) $asOf->format('n')) / 3);
        $labels = ['ANNUAL', 'Q'.$quarter, 'TO DATE'];
        $year = $asOf->format('Y');
        $sectionTitles = [
            'physical_target' => "FY{$year} PHYSICAL TARGET",
            'financial_target' => "FY{$year} FINANCIAL TARGET  ('000)",
            'physical_accomplishment' => "FY{$year} PHYSICAL ACCOMPLISHMENT",
            'financial_accomplishment' => "FY{$year} FINANCIAL ACCOMPLISHMENT  ('000)",
        ];

        foreach (self::SUMMARY_COLUMNS as $kind => $columns) {
            $titleReferences = array_map(fn (string $column): string => $column.'7', $columns);
            $periodReferences = array_map(fn (string $column): string => $column.'8', $columns);
            $titleStyleSource = $this->firstPopulatedCell($xml, $titleReferences) ?? $titleReferences[0];
            $periodStyleSource = $this->firstPopulatedCell($xml, $periodReferences) ?? $periodReferences[0];

            $xml = $this->copyCellStyle($xml, $titleStyleSource, $titleReferences);
            $xml = $this->setInlineCell($xml, $titleReferences[0], $sectionTitles[$kind]);
            foreach (array_slice($titleReferences, 1) as $reference) {
                $xml = $this->clearCell($xml, $reference);
            }

            $xml = $this->copyCellStyle($xml, $periodStyleSource, $periodReferences);
            foreach ($columns as $index => $column) {
                $xml = $this->setInlineCell($xml, $column.'8', $labels[$index]);
                $xml = $this->clearCell($xml, $column.'9');
            }
        }

        return $this->replaceSummaryHeaderMerges($xml);
    }

    /** @param array<int, string> $references */
    private function firstPopulatedCell(string $xml, array $references): ?string
    {
        foreach ($references as $reference) {
            $pattern = '/<c\b(?=[^>]*\br="'.preg_quote($reference, '/').'")[^>]*(?<!\/)>(.*?)<\/c>/s';
            if (! preg_match($pattern, $xml, $matches)) {
                continue;
            }
            if (preg_match('/<(?:v|t)\b[^>]*>\s*[^<\s][^<]*<\/(?:v|t)>/s', $matches[1])) {
                return $reference;
            }
        }

        return null;
    }

    private function collapseMonthlyColumns(string $xml): string
    {
        $hiddenRanges = [[12, 25], [31, 46], [50, 64], [69, 82]];
        $visibleSummaryColumns = array_fill_keys([9, 10, 11, 28, 29, 30, 47, 48, 49, 66, 67, 68], true);

        return preg_replace_callback('/<cols>(.*?)<\/cols>/', function (array $matches) use ($hiddenRanges, $visibleSummaryColumns): string {
            preg_match_all('/<col\b([^>]*)\/>/', $matches[1], $columnMatches);
            $columns = [];
            $maxColumn = 0;

            foreach ($columnMatches[1] as $attributeText) {
                preg_match_all('/([A-Za-z:]+)="([^"]*)"/', $attributeText, $attributeMatches, PREG_SET_ORDER);
                $attributes = [];
                foreach ($attributeMatches as $attribute) {
                    $attributes[$attribute[1]] = $attribute[2];
                }
                $min = (int) ($attributes['min'] ?? 0);
                $max = (int) ($attributes['max'] ?? $min);
                unset($attributes['min'], $attributes['max']);
                $maxColumn = max($maxColumn, $max);
                for ($column = $min; $column <= $max; $column++) {
                    $columns[$column] = $attributes;
                }
            }

            $result = '<cols>';
            for ($column = 1; $column <= $maxColumn; $column++) {
                $attributes = $columns[$column] ?? [];
                $shouldHide = collect($hiddenRanges)->contains(
                    fn (array $range): bool => $column >= $range[0] && $column <= $range[1]
                );
                if ($shouldHide) {
                    $attributes['hidden'] = '1';
                } elseif (isset($visibleSummaryColumns[$column])) {
                    unset($attributes['hidden']);
                    $attributes['width'] = '14';
                    $attributes['customWidth'] = '1';
                }

                $result .= '<col min="'.$column.'" max="'.$column.'"';
                foreach ($attributes as $name => $value) {
                    $result .= ' '.$name.'="'.htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'"';
                }
                $result .= '/>';
            }

            return $result.'</cols>';
        }, $xml) ?? $xml;
    }

    private function replaceSummaryHeaderMerges(string $xml): string
    {
        $blocks = [[9, 25], [28, 45], [47, 64], [66, 82]];
        $summaryMerges = [
            'I7:K7', 'I8:I9', 'J8:J9', 'K8:K9',
            'AB7:AD7', 'AB8:AB9', 'AC8:AC9', 'AD8:AD9',
            'AU7:AW7', 'AU8:AU9', 'AV8:AV9', 'AW8:AW9',
            'BN7:BP7', 'BN8:BN9', 'BO8:BO9', 'BP8:BP9',
        ];

        return preg_replace_callback('/<mergeCells\b[^>]*>(.*?)<\/mergeCells>/', function (array $matches) use ($blocks, $summaryMerges): string {
            preg_match_all('/<mergeCell ref="([^"]+)"\/>/', $matches[1], $mergeMatches);
            $kept = [];
            foreach ($mergeMatches[1] as $reference) {
                if (! preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $reference, $parts)) {
                    $kept[] = $reference;

                    continue;
                }
                $startColumn = $this->columnNumber($parts[1]);
                $startRow = (int) $parts[2];
                $endColumn = $this->columnNumber($parts[3]);
                $endRow = (int) $parts[4];
                $touchesHeader = $startRow <= 9 && $endRow >= 7;
                $touchesSummaryBlock = collect($blocks)->contains(
                    fn (array $block): bool => $startColumn <= $block[1] && $endColumn >= $block[0]
                );
                if (! ($touchesHeader && $touchesSummaryBlock)) {
                    $kept[] = $reference;
                }
            }

            $merges = [...$kept, ...$summaryMerges];
            $content = implode('', array_map(
                fn (string $reference): string => '<mergeCell ref="'.$reference.'"/>',
                $merges
            ));

            return '<mergeCells count="'.count($merges).'">'.$content.'</mergeCells>';
        }, $xml) ?? $xml;
    }

    private function setInlineCell(string $xml, string $reference, string $value): string
    {
        $pattern = '/<c\b((?=[^>]*\br="'.preg_quote($reference, '/').'")[^>]*?)(?:\/>|>.*?<\/c>)/';

        return preg_replace_callback($pattern, function (array $matches) use ($value): string {
            $attributes = preg_replace('/\s+t="[^"]*"/', '', $matches[1]) ?? $matches[1];

            return '<c'.$attributes.' t="inlineStr"><is><t xml:space="preserve">'
                .htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                .'</t></is></c>';
        }, $xml, 1) ?? $xml;
    }

    private function clearCell(string $xml, string $reference): string
    {
        $pattern = '/<c\b((?=[^>]*\br="'.preg_quote($reference, '/').'")[^>]*?)(?:\/>|>.*?<\/c>)/';

        return preg_replace_callback($pattern, function (array $matches): string {
            $attributes = preg_replace('/\s+t="[^"]*"/', '', $matches[1]) ?? $matches[1];

            return '<c'.$attributes.'/>';
        }, $xml, 1) ?? $xml;
    }

    /** @param array<int, string> $targets */
    private function copyCellStyle(string $xml, string $source, array $targets): string
    {
        if (! preg_match(
            '/<c\b(?=[^>]*\br="'.preg_quote($source, '/').'")[^>]*\bs="([0-9]+)"/',
            $xml,
            $sourceMatch
        )) {
            return $xml;
        }

        $style = $sourceMatch[1];
        foreach ($targets as $target) {
            $pattern = '/<c\b(?=[^>]*\br="'.preg_quote($target, '/').'")[^>]*/';
            $xml = preg_replace_callback($pattern, function (array $matches) use ($style): string {
                if (preg_match('/\bs="[0-9]+"/', $matches[0])) {
                    return preg_replace('/\bs="[0-9]+"/', 's="'.$style.'"', $matches[0], 1) ?? $matches[0];
                }

                return $matches[0].' s="'.$style.'"';
            }, $xml, 1) ?? $xml;
        }

        return $xml;
    }

    private function removeCalculationChain(ZipArchive $zip): void
    {
        $zip->deleteName('xl/calcChain.xml');

        $relationshipsPath = 'xl/_rels/workbook.xml.rels';
        $relationshipsXml = $zip->getFromName($relationshipsPath);
        if ($relationshipsXml !== false) {
            $relationshipsXml = preg_replace(
                '/<Relationship\b(?=[^>]*\bType="[^"]*\/calcChain")[^>]*\/>/',
                '',
                $relationshipsXml
            );
            if (is_string($relationshipsXml)) {
                $zip->addFromString($relationshipsPath, $relationshipsXml);
            }
        }

        $contentTypesPath = '[Content_Types].xml';
        $contentTypesXml = $zip->getFromName($contentTypesPath);
        if ($contentTypesXml !== false) {
            $contentTypesXml = preg_replace(
                '/<Override\b(?=[^>]*\bPartName="\/xl\/calcChain\.xml")[^>]*\/>/',
                '',
                $contentTypesXml
            );
            if (is_string($contentTypesXml)) {
                $zip->addFromString($contentTypesPath, $contentTypesXml);
            }
        }
    }

    private function sheetPath(ZipArchive $zip, string $sheetName): string
    {
        $workbook = simplexml_load_string((string) $zip->getFromName('xl/workbook.xml'));
        $relationships = simplexml_load_string((string) $zip->getFromName('xl/_rels/workbook.xml.rels'));
        $relationshipMap = [];
        foreach ($relationships->Relationship as $relationship) {
            $attributes = $relationship->attributes();
            $target = ltrim((string) $attributes['Target'], '/');
            $relationshipMap[(string) $attributes['Id']] = str_starts_with($target, 'xl/')
                ? $target
                : 'xl/'.$target;
        }
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        foreach ($workbook->sheets->sheet as $sheet) {
            if (strcasecmp(trim((string) $sheet['name']), trim($sheetName)) !== 0) {
                continue;
            }
            $relationshipAttributes = $sheet->attributes('r', true);

            return $relationshipMap[(string) $relationshipAttributes['id']]
                ?? throw new RuntimeException("The {$sheetName} worksheet relationship is missing.");
        }
        throw new RuntimeException("The {$sheetName} worksheet is missing from the official template.");
    }

    private function columnNumber(string $column): int
    {
        $number = 0;
        foreach (str_split($column) as $character) {
            $number = ($number * 26) + ord($character) - 64;
        }

        return $number;
    }

    private function normalizeText(string $value): string
    {
        return preg_replace('/[^A-Z0-9]+/', '', mb_strtoupper($value)) ?? '';
    }

    private function normalizeOffice(string $value): string
    {
        return $this->normalizeText($value);
    }

    private function officeAlias(string $office): string
    {
        return match ($office) {
            'REGIONALOFFICE' => 'RO',
            'MOUNTAINPROVINCE', 'MTPROV', 'MTPROVINCE', 'MPROVINCE' => 'MTPROVINCE',
            'ALISTA', 'ALFONSOLISTA' => 'ALFONSOLISTA',
            'CPARACELIS' => 'PARACELIS',
            'CSABANGAN' => 'SABANGAN',
            default => $office,
        };
    }
}
