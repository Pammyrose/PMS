<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class PhysicalPerformanceSectionFormatter
{
    private const MAIN_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    /**
     * Reformat the physical-performance portion and, when supplied, append the
     * compact financial-performance summary used by the generated workbook.
     * Cells in columns A:C are copied from the original export unchanged.
     *
     * @param  array{0:string,1:string,2:string}  $targetColumns
     * @param  array{0:string,1:string,2:string}  $accomplishmentColumns
     * @param  array{0:string,1:string,2:string}|null  $financialTargetColumns
     * @param  array{0:string,1:string,2:string}|null  $financialAccomplishmentColumns
     */
    public function format(
        string $path,
        string $sheetName,
        array $targetColumns,
        array $accomplishmentColumns,
        int $dataStartRow,
        ?array $financialTargetColumns = null,
        ?array $financialAccomplishmentColumns = null
    ): void {
        $sourceSheetRows = iterator_to_array(
            (new SimpleXlsxReader)->rows($path, $sheetName, false)
        );
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to open the Excel workbook for physical-performance formatting.');
        }

        try {
            $sheetPath = $this->sheetPath($zip, $sheetName);
            $worksheetXml = $zip->getFromName($sheetPath);
            $stylesXml = $zip->getFromName('xl/styles.xml');
            if ($worksheetXml === false || $stylesXml === false) {
                throw new RuntimeException('The workbook worksheet or styles could not be read.');
            }

            $worksheet = $this->document($worksheetXml);
            $xpath = $this->xpath($worksheet);
            $baseNumberStyle = $this->firstCellStyle(
                $xpath,
                $targetColumns[0],
                $dataStartRow
            );
            [$stylesXml, $percentStyle] = $this->appendPercentageStyle($stylesXml, $baseNumberStyle);
            $formatted = $this->performanceWorksheet(
                $worksheet,
                $xpath,
                $targetColumns,
                $accomplishmentColumns,
                $dataStartRow,
                $percentStyle,
                $financialTargetColumns,
                $financialAccomplishmentColumns,
                $sourceSheetRows
            );

            $zip->addFromString($sheetPath, $formatted);
            $zip->addFromString('xl/styles.xml', $stylesXml);
        } finally {
            $zip->close();
        }
    }

    /**
     * @param  array{0:string,1:string,2:string}  $targetColumns
     * @param  array{0:string,1:string,2:string}  $accomplishmentColumns
     */
    private function performanceWorksheet(
        DOMDocument $worksheet,
        DOMXPath $xpath,
        array $targetColumns,
        array $accomplishmentColumns,
        int $dataStartRow,
        int $percentStyle,
        ?array $financialTargetColumns,
        ?array $financialAccomplishmentColumns,
        array $sourceSheetRows
    ): string {
        $sourceRows = [];
        foreach ($xpath->query('//x:sheetData/x:row') ?: [] as $row) {
            if (! $row instanceof DOMElement) {
                continue;
            }
            $rowNumber = (int) $row->getAttribute('r');
            $sourceRows[$rowNumber] = $row;
        }

        $lastSourceRow = $sourceRows === [] ? $dataStartRow : max(array_keys($sourceRows));
        $lastRow = max($dataStartRow, $lastSourceRow);
        $physicalLayoutColumns = $this->physicalLayoutColumns();
        $includeFinancial = $financialTargetColumns !== null
            && $financialAccomplishmentColumns !== null;
        $financialLayoutColumns = $includeFinancial ? $this->financialLayoutColumns() : [];
        $headerStyle = $this->firstAvailableStyle($xpath, ['D7', 'A7'], 0);
        $subheaderStyle = $this->firstAvailableStyle($xpath, ['D9', 'D8', 'A9'], $headerStyle);

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="'.self::MAIN_NS.'">';
        $xml .= '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>';
        $xml .= '<dimension ref="A1:'.$this->performanceEndColumn($includeFinancial).$lastRow.'"/>';
        $freezeRow = max(1, $dataStartRow - 1);
        $xml .= '<sheetViews><sheetView workbookViewId="0" showGridLines="1" zoomScale="90" zoomScaleNormal="90">';
        $xml .= '<pane ySplit="'.$freezeRow.'" topLeftCell="A'.$dataStartRow.'" activePane="bottomLeft" state="frozen"/>';
        $xml .= '<selection pane="bottomLeft" activeCell="A'.$dataStartRow.'" sqref="A'.$dataStartRow.'"/>';
        $xml .= '</sheetView></sheetViews>';
        $xml .= '<sheetFormatPr customHeight="1" defaultColWidth="12.63" defaultRowHeight="15.75"/>';
        $xml .= $this->columns($xpath, $includeFinancial);
        $xml .= '<sheetData>';

        for ($rowNumber = 1; $rowNumber <= $lastRow; $rowNumber++) {
            $sourceRowNumber = $rowNumber;
            $sourceRow = $sourceRows[$sourceRowNumber] ?? null;
            $rowAttributes = $rowNumber >= 7 && $rowNumber <= 9
                ? $this->performanceHeaderRowAttributes($rowNumber)
                : $this->rowAttributes($sourceRow, true);
            $xml .= '<row r="'.$rowNumber.'"'.$rowAttributes.'>';
            if ($sourceRow instanceof DOMElement) {
                $xml .= $this->copyIdentityCells($worksheet, $sourceRow, $rowNumber);
            }
            if ($rowNumber >= 7 && $rowNumber <= 9) {
                $xml .= $this->performanceHeaderCells(
                    $rowNumber,
                    $headerStyle,
                    $subheaderStyle,
                    $includeFinancial
                );
                $xml .= '</row>';

                continue;
            }
            if ($rowNumber < $dataStartRow) {
                $xml .= '</row>';

                continue;
            }

            $rowCells = $this->rowCells($sourceRow);
            $target = $this->summaryCells($xpath, $targetColumns, $rowCells, $sourceRowNumber);
            $accomplishment = $this->summaryCells(
                $xpath,
                $accomplishmentColumns,
                $rowCells,
                $sourceRowNumber
            );
            $hasPhysicalValue = $target['has_value'] || $accomplishment['has_value'];
            $sectionValues = [
                'physical-target' => array_combine(['annual', 'quarter', 'to-date'], $target['values']),
                'physical-accomplishment' => array_combine(['annual', 'quarter', 'to-date'], $accomplishment['values']),
            ];
            $sectionStyles = [
                'physical-target' => array_combine(['annual', 'quarter', 'to-date'], $target['styles']),
                'physical-accomplishment' => array_combine(['annual', 'quarter', 'to-date'], $accomplishment['styles']),
            ];
            $values = [];
            $styles = [];
            $hasValues = [];
            foreach ($physicalLayoutColumns as $column => $definition) {
                $section = $definition['section'];
                $period = $definition['period'];
                if ($section['isPercentage'] ?? false) {
                    $numerator = $sectionValues[$period['numeratorSection']][$period['numeratorPeriod']] ?? 0.0;
                    $denominator = $sectionValues[$period['denominatorSection']][$period['denominatorPeriod']] ?? 0.0;
                    $values[$column] = $this->percentage((float) $numerator, (float) $denominator);
                    $styles[$column] = $percentStyle;
                    $hasValues[$column] = $hasPhysicalValue;

                    continue;
                }

                $values[$column] = $sectionValues[$section['key']][$period['key']] ?? 0.0;
                $styles[$column] = $sectionStyles[$section['key']][$period['key']] ?? 0;
                $hasValues[$column] = $hasPhysicalValue;
            }

            if ($includeFinancial) {
                $financialTarget = $this->summaryCells(
                    $xpath,
                    $financialTargetColumns,
                    $rowCells,
                    $sourceRowNumber
                );
                $financialAccomplishment = $this->summaryCells(
                    $xpath,
                    $financialAccomplishmentColumns,
                    $rowCells,
                    $sourceRowNumber
                );
                $hasFinancialValue = $financialTarget['has_value']
                    || $financialAccomplishment['has_value'];
                $hasFinancialValue = $hasFinancialValue && $this->isFinancialTotalOffice(
                    (string) ($sourceSheetRows[$sourceRowNumber]['C'] ?? '')
                );
                $financialSections = [
                    'financial-target' => array_combine(
                        ['annual', 'quarter', 'to-date'],
                        $financialTarget['values']
                    ),
                    'financial-accomplishment' => array_combine(
                        ['annual', 'quarter', 'to-date'],
                        $financialAccomplishment['values']
                    ),
                ];
                $financialStyles = [
                    'financial-target' => array_combine(
                        ['annual', 'quarter', 'to-date'],
                        $financialTarget['styles']
                    ),
                    'financial-accomplishment' => array_combine(
                        ['annual', 'quarter', 'to-date'],
                        $financialAccomplishment['styles']
                    ),
                ];

                foreach ($financialLayoutColumns as $column => $definition) {
                    if (($definition['isPercentage'] ?? false) === true) {
                        $numerator = $financialSections[$definition['numeratorSection']][$definition['numeratorPeriod']] ?? 0.0;
                        $denominator = $financialSections[$definition['denominatorSection']][$definition['denominatorPeriod']] ?? 0.0;
                        $values[$column] = $this->percentage(
                            (float) $numerator,
                            (float) $denominator
                        );
                        $styles[$column] = $percentStyle;
                    } else {
                        $values[$column] = $financialSections[$definition['sourceSection']][$definition['sourcePeriod']] ?? 0.0;
                        $styles[$column] = $financialStyles[$definition['sourceSection']][$definition['sourcePeriod']] ?? 0;
                    }
                    $hasValues[$column] = $hasFinancialValue;
                }
            }

            foreach ($values as $column => $value) {
                $reference = $column.$rowNumber;
                if (! ($hasValues[$column] ?? false)) {
                    $xml .= '<c r="'.$reference.'" s="'.(int) $styles[$column].'"/>';
                } else {
                    $xml .= $this->numberCell($reference, $value, (int) $styles[$column]);
                }
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData>';
        $merges = $this->physicalHeaderMerges($physicalLayoutColumns);
        if ($includeFinancial) {
            $merges = [...$merges, ...$this->financialHeaderMerges($financialLayoutColumns)];
        }
        $merges = [...$this->preservedIdentityMerges($xpath, $includeFinancial), ...$merges];
        $merges = array_values(array_unique($merges));
        $xml .= '<mergeCells count="'.count($merges).'">';
        foreach ($merges as $merge) {
            $xml .= '<mergeCell ref="'.$merge.'"/>';
        }
        $xml .= '</mergeCells>';
        $xml .= '<printOptions horizontalCentered="1"/>';
        $xml .= '<pageMargins left="0.25" right="0.25" top="0.75" bottom="0.25" header="0" footer="0"/>';
        $xml .= '<pageSetup orientation="landscape" paperSize="14" fitToWidth="1" fitToHeight="0"/>';
        $xml .= '</worksheet>';

        return $xml;
    }

    private function physicalHeaderCells(int $row, int $headerStyle, int $subheaderStyle): string
    {
        $layoutColumns = $this->physicalLayoutColumns();
        $labels = [];
        if ($row === 7) {
            $labels[array_key_first($layoutColumns)] = strtoupper(PhysicalPerformanceSummaryLayout::title());
        } elseif ($row === 8) {
            foreach ($layoutColumns as $column => $definition) {
                $sectionKey = $definition['section']['key'];
                if (! in_array($sectionKey, array_column($labels, 'section'), true)) {
                    $labels[$column] = [
                        'section' => $sectionKey,
                        'label' => $definition['section']['label'],
                    ];
                }
            }
        } else {
            foreach ($layoutColumns as $column => $definition) {
                $labels[$column] = $definition['period']['excelLabel'] ?? $definition['period']['label'];
            }
        }
        $style = $row === 9 ? $subheaderStyle : $headerStyle;
        $xml = '';
        foreach (array_keys($layoutColumns) as $column) {
            $label = $labels[$column] ?? null;
            if (is_array($label)) {
                $label = $label['label'];
            }
            $xml .= isset($labels[$column])
                ? $this->inlineCell($column.$row, (string) $label, $style)
                : '<c r="'.$column.$row.'" s="'.$style.'"/>';
        }

        return $xml;
    }

    private function performanceHeaderCells(
        int $row,
        int $headerStyle,
        int $subheaderStyle,
        bool $includeFinancial
    ): string {
        $xml = $this->physicalHeaderCells($row, $headerStyle, $subheaderStyle);

        if (! $includeFinancial) {
            return $xml;
        }

        $layoutColumns = $this->financialLayoutColumns();
        $labels = [];
        if ($row === 7) {
            $labels[array_key_first($layoutColumns)] = 'FINANCIAL PERFORMANCE';
        } elseif ($row === 8) {
            foreach ($layoutColumns as $column => $definition) {
                $section = $definition['section'];
                if (! in_array($section, $labels, true)) {
                    $labels[$column] = $section;
                }
            }
        } else {
            foreach ($layoutColumns as $column => $definition) {
                $labels[$column] = $definition['periodLabel'];
            }
        }

        $style = $row === 9 ? $subheaderStyle : $headerStyle;
        foreach (array_keys($layoutColumns) as $column) {
            $label = $labels[$column] ?? '';
            $xml .= $label !== ''
                ? $this->inlineCell($column.$row, $label, $style)
                : '<c r="'.$column.$row.'" s="'.$style.'"/>';
        }

        return $xml;
    }

    /** @return array<string, array{section:array<string, mixed>,period:array<string, mixed>}> */
    private function physicalLayoutColumns(): array
    {
        $columns = [];
        $columnNumber = 4;
        foreach (PhysicalPerformanceSummaryLayout::sections() as $section) {
            foreach ($section['periods'] ?? [] as $period) {
                $columns[$this->columnName($columnNumber++)] = [
                    'section' => $section,
                    'period' => $period,
                ];
            }
        }

        return $columns;
    }

    /** @return array<string, array<string, mixed>> */
    private function financialLayoutColumns(): array
    {
        return [
            'K' => [
                'section' => 'Obligation',
                'periodLabel' => 'Allotment',
                'sourceSection' => 'financial-target',
                'sourcePeriod' => 'annual',
            ],
            'L' => [
                'section' => 'Obligation',
                'periodLabel' => "This\nQuarter",
                'sourceSection' => 'financial-target',
                'sourcePeriod' => 'quarter',
            ],
            'M' => [
                'section' => 'Obligation',
                'periodLabel' => 'To Date',
                'sourceSection' => 'financial-target',
                'sourcePeriod' => 'to-date',
            ],
            'N' => [
                'section' => 'Disbursement',
                'periodLabel' => "This\nQuarter",
                'sourceSection' => 'financial-accomplishment',
                'sourcePeriod' => 'quarter',
            ],
            'O' => [
                'section' => 'Disbursement',
                'periodLabel' => 'To Date',
                'sourceSection' => 'financial-accomplishment',
                'sourcePeriod' => 'to-date',
            ],
            'P' => [
                'section' => '% Budget Utilization Rate (BUR)',
                'periodLabel' => "(Oblig/\nAllot)\n*100",
                'isPercentage' => true,
                'numeratorSection' => 'financial-target',
                'numeratorPeriod' => 'to-date',
                'denominatorSection' => 'financial-target',
                'denominatorPeriod' => 'annual',
            ],
            'Q' => [
                'section' => '% Budget Utilization Rate (BUR)',
                'periodLabel' => "(Disb/\nAllot)\n*100",
                'isPercentage' => true,
                'numeratorSection' => 'financial-accomplishment',
                'numeratorPeriod' => 'to-date',
                'denominatorSection' => 'financial-target',
                'denominatorPeriod' => 'annual',
            ],
            'R' => [
                'section' => '% Budget Utilization Rate (BUR)',
                'periodLabel' => "(Disb/\nOblig)\n*100",
                'isPercentage' => true,
                'numeratorSection' => 'financial-accomplishment',
                'numeratorPeriod' => 'to-date',
                'denominatorSection' => 'financial-target',
                'denominatorPeriod' => 'to-date',
            ],
        ];
    }

    /**
     * @param  array<string, array{section:array<string, mixed>,period:array<string, mixed>}>  $layoutColumns
     * @return array<int, string>
     */
    private function physicalHeaderMerges(array $layoutColumns): array
    {
        $columns = array_keys($layoutColumns);
        if ($columns === []) {
            return [];
        }

        $merges = [$columns[0].'7:'.$columns[array_key_last($columns)].'7'];
        $sectionRanges = [];
        foreach ($layoutColumns as $column => $definition) {
            $sectionKey = $definition['section']['key'];
            $sectionRanges[$sectionKey] ??= ['start' => $column, 'end' => $column];
            $sectionRanges[$sectionKey]['end'] = $column;
        }
        foreach ($sectionRanges as $range) {
            if ($range['start'] !== $range['end']) {
                $merges[] = $range['start'].'8:'.$range['end'].'8';
            }
        }

        return $merges;
    }

    /**
     * @param  array<string, array<string, mixed>>  $layoutColumns
     * @return array<int, string>
     */
    private function financialHeaderMerges(array $layoutColumns): array
    {
        $columns = array_keys($layoutColumns);
        if ($columns === []) {
            return [];
        }

        $merges = [$columns[0].'7:'.$columns[array_key_last($columns)].'7'];
        $sectionRanges = [];
        foreach ($layoutColumns as $column => $definition) {
            $section = $definition['section'];
            $sectionRanges[$section] ??= ['start' => $column, 'end' => $column];
            $sectionRanges[$section]['end'] = $column;
        }
        foreach ($sectionRanges as $range) {
            $merges[] = $range['start'] === $range['end']
                ? $range['start'].'8:'.$range['end'].'9'
                : $range['start'].'8:'.$range['end'].'8';
        }

        return $merges;
    }

    private function physicalEndColumn(): string
    {
        return $this->columnName(3 + PhysicalPerformanceSummaryLayout::columnCount());
    }

    private function performanceEndColumn(bool $includeFinancial): string
    {
        return $includeFinancial ? 'R' : $this->physicalEndColumn();
    }

    private function columnName(int $number): string
    {
        $column = '';
        while ($number > 0) {
            $number--;
            $column = chr(65 + ($number % 26)).$column;
            $number = intdiv($number, 26);
        }

        return $column;
    }

    /** @return array{values:array{0:float,1:float,2:float},styles:array{0:int,1:int,2:int},has_value:bool} */
    private function summaryCells(DOMXPath $xpath, array $columns, array $rowCells, int $row): array
    {
        $values = [];
        $styles = [];
        $hasValue = false;
        foreach ($columns as $column) {
            $cell = $rowCells[$column.$row] ?? null;
            $valueNode = $cell instanceof DOMElement
                ? $xpath->query('./x:v', $cell)?->item(0)
                : null;
            $raw = $valueNode?->textContent ?? '';
            $values[] = is_numeric($raw) ? (float) $raw : 0.0;
            $styles[] = $cell instanceof DOMElement ? (int) ($cell->getAttribute('s') ?: 0) : 0;
            $hasValue = $hasValue || is_numeric($raw);
        }

        return ['values' => $values, 'styles' => $styles, 'has_value' => $hasValue];
    }

    /** @return array<string, DOMElement> */
    private function rowCells(?DOMElement $row): array
    {
        if (! $row instanceof DOMElement) {
            return [];
        }

        $cells = [];
        foreach ($row->childNodes as $cell) {
            if ($cell instanceof DOMElement && $cell->localName === 'c') {
                $cells[$cell->getAttribute('r')] = $cell;
            }
        }

        return $cells;
    }

    private function copyIdentityCells(DOMDocument $document, DOMElement $row, int $newRow): string
    {
        $xml = '';
        foreach ($row->childNodes as $cell) {
            if (! $cell instanceof DOMElement || $cell->localName !== 'c') {
                continue;
            }
            $reference = $cell->getAttribute('r');
            if (! preg_match('/^([A-C])(\d+)$/', $reference, $matches)) {
                continue;
            }
            $clone = $cell->cloneNode(true);
            if ($clone instanceof DOMElement) {
                $clone->setAttribute('r', $matches[1].$newRow);
                $xml .= $document->saveXML($clone);
            }
        }

        return $xml;
    }

    private function columns(DOMXPath $xpath, bool $includeFinancial): string
    {
        $xml = '<cols>';
        foreach ($xpath->query('//x:cols/x:col') ?: [] as $column) {
            if (! $column instanceof DOMElement) {
                continue;
            }
            $min = (int) $column->getAttribute('min');
            $max = (int) $column->getAttribute('max');
            if ($min > 3 || $max < 1) {
                continue;
            }
            $width = $column->getAttribute('width') ?: '12.63';
            $xml .= '<col min="'.max(1, $min).'" max="'.min(3, $max).'" width="'
                .$this->escape($width).'" customWidth="1"/>';
        }
        $xml .= '<col min="4" max="'.(3 + PhysicalPerformanceSummaryLayout::columnCount()).'" width="8.63" customWidth="1"/>';
        if ($includeFinancial) {
            $xml .= '<col min="11" max="15" width="10.63" customWidth="1"/>';
            $xml .= '<col min="16" max="18" width="12.63" customWidth="1"/>';
        }

        return $xml.'</cols>';
    }

    /** @return array<int, string> */
    private function preservedIdentityMerges(DOMXPath $xpath, bool $includeFinancial): array
    {
        $merges = [];
        foreach ($xpath->query('//x:mergeCells/x:mergeCell') ?: [] as $merge) {
            if (! $merge instanceof DOMElement) {
                continue;
            }
            $reference = $merge->getAttribute('ref');
            if (! preg_match('/^([A-Z]+)(\d+):([A-Z]+)(\d+)$/', $reference, $matches)) {
                continue;
            }
            $startColumn = $this->columnNumber($matches[1]);
            $endColumn = $this->columnNumber($matches[3]);
            if ($startColumn > 3) {
                continue;
            }
            if ($endColumn <= 3) {
                $merges[] = $reference;

                continue;
            }
            if ((int) $matches[2] < 7 && $matches[2] === $matches[4]) {
                $merges[] = $matches[1].$matches[2].':'
                    .$this->performanceEndColumn($includeFinancial).$matches[4];
            }
        }

        return $merges;
    }

    private function columnNumber(string $column): int
    {
        $number = 0;
        foreach (str_split($column) as $character) {
            $number = ($number * 26) + ord($character) - 64;
        }

        return $number;
    }

    /** @return array{0:string,1:int} */
    private function appendPercentageStyle(string $stylesXml, int $baseStyle): array
    {
        $styles = $this->document($stylesXml);
        $xpath = $this->xpath($styles);
        $root = $styles->documentElement;
        $cellXfs = $xpath->query('//x:cellXfs')?->item(0);
        if (! $root instanceof DOMElement || ! $cellXfs instanceof DOMElement) {
            throw new RuntimeException('The workbook styles are invalid.');
        }

        $numFmts = $xpath->query('//x:numFmts')?->item(0);
        if (! $numFmts instanceof DOMElement) {
            $numFmts = $styles->createElementNS(self::MAIN_NS, 'numFmts');
            $numFmts->setAttribute('count', '0');
            $root->insertBefore($numFmts, $root->firstChild);
        }
        $numberFormatId = 200;
        foreach ($xpath->query('//x:numFmts/x:numFmt') ?: [] as $format) {
            if ($format instanceof DOMElement) {
                $numberFormatId = max($numberFormatId, ((int) $format->getAttribute('numFmtId')) + 1);
            }
        }
        $format = $styles->createElementNS(self::MAIN_NS, 'numFmt');
        $format->setAttribute('numFmtId', (string) $numberFormatId);
        $format->setAttribute('formatCode', '0.00%');
        $numFmts->appendChild($format);
        $numFmts->setAttribute('count', (string) $numFmts->childElementCount);

        $xfs = $xpath->query('//x:cellXfs/x:xf');
        $styleCount = $xfs?->length ?? 0;
        $base = $xfs?->item(min(max(0, $baseStyle), max(0, $styleCount - 1)));
        $percentageXf = $base instanceof DOMElement
            ? $base->cloneNode(true)
            : $styles->createElementNS(self::MAIN_NS, 'xf');
        if (! $percentageXf instanceof DOMElement) {
            throw new RuntimeException('Unable to create the percentage style.');
        }
        $percentageXf->setAttribute('numFmtId', (string) $numberFormatId);
        $percentageXf->setAttribute('applyNumberFormat', '1');
        $cellXfs->appendChild($percentageXf);
        $cellXfs->setAttribute('count', (string) ($styleCount + 1));

        return [$styles->saveXML(), $styleCount];
    }

    private function firstCellStyle(DOMXPath $xpath, string $column, int $startRow): int
    {
        $nodes = $xpath->query("//x:sheetData/x:row[@r >= {$startRow}]/x:c[starts-with(@r, '{$column}')]");
        foreach ($nodes ?: [] as $cell) {
            if ($cell instanceof DOMElement && $xpath->query('./x:v', $cell)?->length) {
                return (int) ($cell->getAttribute('s') ?: 0);
            }
        }

        return 0;
    }

    /** @param array<int, string> $references */
    private function firstAvailableStyle(DOMXPath $xpath, array $references, int $fallback): int
    {
        foreach ($references as $reference) {
            $cell = $xpath->query("//x:c[@r='{$reference}']")?->item(0);
            if ($cell instanceof DOMElement) {
                return (int) ($cell->getAttribute('s') ?: 0);
            }
        }

        return $fallback;
    }

    private function rowAttributes(?DOMElement $row, bool $includeHidden): string
    {
        if (! $row instanceof DOMElement) {
            return '';
        }

        $attributes = '';
        foreach (['ht', 'customHeight'] as $name) {
            if ($row->hasAttribute($name)) {
                $attributes .= ' '.$name.'="'.$this->escape($row->getAttribute($name)).'"';
            }
        }
        if ($includeHidden && $row->getAttribute('hidden') === '1') {
            $attributes .= ' hidden="1"';
        }

        return $attributes;
    }

    private function performanceHeaderRowAttributes(int $row): string
    {
        $height = match ($row) {
            7 => '22',
            8 => '24',
            9 => '48',
            default => '15.75',
        };

        return ' ht="'.$height.'" customHeight="1"';
    }

    private function isFinancialTotalOffice(string $office): bool
    {
        $normalized = strtoupper(trim($office));
        $normalized = preg_replace('/\b(?:PENRO|CENRO|TOTAL)\b/', '', $normalized) ?? '';
        $normalized = preg_replace('/[^A-Z0-9]+/', '', $normalized) ?? '';

        return in_array($normalized, [
            'CAR',
            'RO',
            'REGIONALOFFICE',
            'ABRA',
            'APAYAO',
            'BENGUET',
            'IFUGAO',
            'KALINGA',
            'MTPROVINCE',
            'MOUNTAINPROVINCE',
        ], true);
    }

    private function percentage(float $accomplishment, float $target): float
    {
        return $target == 0.0 ? 0.0 : $accomplishment / $target;
    }

    private function inlineCell(string $reference, string $value, int $style): string
    {
        return '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'
            .$this->escape($value).'</t></is></c>';
    }

    private function numberCell(string $reference, float $value, int $style): string
    {
        $number = rtrim(rtrim(number_format($value, 10, '.', ''), '0'), '.');

        return '<c r="'.$reference.'" s="'.$style.'"><v>'
            .($number === '' || $number === '-0' ? '0' : $number).'</v></c>';
    }

    private function sheetPath(ZipArchive $zip, string $sheetName): string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relationshipsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relationshipsXml === false) {
            throw new RuntimeException('The workbook relationships could not be read.');
        }

        $workbook = $this->document($workbookXml);
        $workbookXpath = $this->xpath($workbook);
        $relationshipId = null;
        foreach ($workbookXpath->query('//x:sheets/x:sheet') ?: [] as $sheet) {
            if ($sheet instanceof DOMElement && strcasecmp($sheet->getAttribute('name'), $sheetName) === 0) {
                $relationshipId = $sheet->getAttributeNS(
                    'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                    'id'
                );
                break;
            }
        }
        if ($relationshipId === null || $relationshipId === '') {
            throw new RuntimeException("The {$sheetName} worksheet is missing.");
        }

        $relationships = $this->document($relationshipsXml);
        $relationshipXpath = new DOMXPath($relationships);
        foreach ($relationshipXpath->query("//*[local-name()='Relationship']") ?: [] as $relationship) {
            if ($relationship instanceof DOMElement && $relationship->getAttribute('Id') === $relationshipId) {
                return 'xl/'.ltrim($relationship->getAttribute('Target'), '/');
            }
        }

        throw new RuntimeException("The {$sheetName} worksheet relationship is missing.");
    }

    private function document(string $xml): DOMDocument
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        if (! $document->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT)) {
            throw new RuntimeException('Unable to parse the Excel XML.');
        }

        return $document;
    }

    private function xpath(DOMDocument $document): DOMXPath
    {
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('x', self::MAIN_NS);

        return $xpath;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
