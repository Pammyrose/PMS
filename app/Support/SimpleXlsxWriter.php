<?php

namespace App\Support;

use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use ZipArchive;

class SimpleXlsxWriter
{
    private const LAST_COLUMN = 'O';

    private const MONTH_KEYS = [
        'jan', 'feb', 'mar',
        'apr', 'may', 'jun',
        'jul', 'aug', 'sep',
        'oct', 'nov', 'dec',
    ];

    /**
     * Create a single-sheet WFP workbook using the layout of the official DENR form.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function writeWfp(
        string $path,
        string $sheetName,
        string $sectorLabel,
        int $year,
        array $rows,
        ?DateTimeInterface $asOf = null
    ): void {
        $asOf ??= new DateTimeImmutable;
        $zip = new ZipArchive;
        $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            throw new RuntimeException('Unable to create the Excel workbook.');
        }

        try {
            $zip->addFromString('[Content_Types].xml', $this->contentTypes());
            $zip->addFromString('_rels/.rels', $this->rootRelationships());
            $zip->addFromString('docProps/app.xml', $this->appProperties($sheetName));
            $zip->addFromString('docProps/core.xml', $this->coreProperties());
            $zip->addFromString('xl/workbook.xml', $this->workbook($sheetName));
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
            $zip->addFromString('xl/styles.xml', $this->styles());
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheet($sectorLabel, $year, $rows, $asOf));
        } finally {
            $zip->close();
        }

        if (! is_file($path) || filesize($path) === 0) {
            throw new RuntimeException('The Excel workbook could not be written.');
        }
    }

    private function worksheet(
        string $sectorLabel,
        int $year,
        array $rows,
        DateTimeInterface $asOf
    ): string {
        $lastRow = max(10, 9 + count($rows));
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>';
        $xml .= '<dimension ref="A1:'.self::LAST_COLUMN.$lastRow.'"/>';
        $xml .= '<sheetViews><sheetView workbookViewId="0" tabSelected="1" showGridLines="1" zoomScale="85" zoomScaleNormal="85">';
        $xml .= '<pane xSplit="3" ySplit="9" topLeftCell="D10" activePane="bottomRight" state="frozen"/>';
        $xml .= '<selection pane="topRight" activeCell="D1" sqref="D1"/>';
        $xml .= '<selection pane="bottomLeft" activeCell="A10" sqref="A10"/>';
        $xml .= '<selection pane="bottomRight" activeCell="D10" sqref="D10"/>';
        $xml .= '</sheetView></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="15"/>';
        $xml .= $this->columns();
        $xml .= '<sheetData>';
        $xml .= $this->titleRows($sectorLabel, $year);
        $xml .= $this->headerRows($asOf);

        if ($rows === []) {
            $xml .= '<row r="10" ht="28" customHeight="1">';
            $xml .= $this->inlineCell('A10', 'No saved data is available for this year.', 7);
            $xml .= '</row>';
        } else {
            foreach (array_values($rows) as $index => $row) {
                $xml .= $this->dataRow(10 + $index, $row, $asOf);
            }
        }

        $xml .= '</sheetData>';
        $merges = $this->merges();
        $xml .= '<mergeCells count="'.count($merges).'">';
        foreach ($merges as $range) {
            $xml .= '<mergeCell ref="'.$range.'"/>';
        }
        $xml .= '</mergeCells>';
        $xml .= '<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>';
        $xml .= '<pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>';
        $xml .= '</worksheet>';

        return $xml;
    }

    private function titleRows(string $sectorLabel, int $year): string
    {
        return '<row r="1" ht="22" customHeight="1">'.$this->inlineCell('A1', 'DEPARTMENT OF ENVIRONMENT AND NATURAL RESOURCES', 1).'</row>'
            .'<row r="2" ht="22" customHeight="1">'.$this->inlineCell('A2', "FY {$year} WORK AND FINANCIAL PLAN", 1).'</row>'
            .'<row r="3" ht="18" customHeight="1">'.$this->inlineCell('A3', '(In Thousand Pesos)', 2).'</row>'
            .'<row r="4"/>'
            .'<row r="5" ht="20" customHeight="1">'.$this->inlineCell('A5', 'REGION/BUREAU: Cordillera Administrative Region (CAR)', 3).'</row>'
            .'<row r="6" ht="18" customHeight="1">'.$this->inlineCell('A6', $sectorLabel, 3).'</row>';
    }

    private function headerRows(DateTimeInterface $asOf): string
    {
        $month = (int) $asOf->format('n');
        $quarter = (int) ceil($month / 3);
        $periodLabels = ['ANNUAL', 'Q'.$quarter, 'TO DATE'];
        $sections = [
            ['start' => 4, 'label' => 'Physical Target', 'style' => 5],
            ['start' => 7, 'label' => 'Financial Target', 'style' => 7],
            ['start' => 10, 'label' => 'Physical Accomplishment', 'style' => 6],
            ['start' => 13, 'label' => 'Financial Accomplishment', 'style' => 8],
        ];

        $xml = '<row r="7" ht="24" customHeight="1">';
        $xml .= $this->inlineCell('A7', 'PROGRAM/ACTIVITY/PROJECT', 4);
        $xml .= $this->inlineCell('B7', 'PERFORMANCE INDICATOR', 4);
        $xml .= $this->inlineCell('C7', 'LOCATION (Province)', 4);
        foreach ($sections as $section) {
            $column = $this->columnName($section['start']);
            $xml .= $this->inlineCell($column.'7', $section['label'], $section['style']);
        }
        $xml .= '</row>';

        $xml .= '<row r="8" ht="24" customHeight="1">';
        foreach ($sections as $section) {
            foreach ($periodLabels as $offset => $label) {
                $column = $this->columnName($section['start'] + $offset);
                $xml .= $this->inlineCell($column.'8', $label, $section['style']);
            }
        }

        return $xml.'</row><row r="9" ht="8" customHeight="1"/>';
    }

    /** @param array<string, mixed> $row */
    private function dataRow(int $rowNumber, array $row, DateTimeInterface $asOf): string
    {
        $values = [
            'A' => $row['pap'] ?? '',
            'B' => $row['indicator'] ?? '',
            'C' => $row['office'] ?? '',
        ];

        $indicatorType = (string) ($row['indicator_type'] ?? 'cumulative');
        $this->putSummary($values, $row['physical_target'] ?? [], 'D', $asOf, $indicatorType);
        $this->putSummary($values, $row['financial_target'] ?? [], 'G', $asOf, 'cumulative');
        $this->putSummary($values, $row['physical_accomplishment'] ?? [], 'J', $asOf, $indicatorType);
        $this->putSummary($values, $row['financial_accomplishment'] ?? [], 'M', $asOf, 'cumulative');

        $isCar = strcasecmp((string) ($row['office'] ?? ''), 'CAR') === 0;
        $xml = '<row r="'.$rowNumber.'" ht="30" customHeight="1">';
        $lastColumn = $this->columnNumber(self::LAST_COLUMN);

        for ($column = 1; $column <= $lastColumn; $column++) {
            $name = $this->columnName($column);
            $reference = $name.$rowNumber;
            $value = $values[$name] ?? '';

            if (is_int($value) || is_float($value)) {
                $xml .= $this->numberCell($reference, $value, $isCar ? 13 : 11);
            } elseif (is_numeric($value) && trim((string) $value) !== '') {
                $xml .= $this->numberCell($reference, (float) $value, $isCar ? 13 : 11);
            } else {
                $style = $isCar ? 12 : (in_array($name, ['A', 'B'], true) ? 9 : 10);
                $xml .= $this->inlineCell($reference, (string) $value, $style);
            }
        }

        return $xml.'</row>';
    }

    /**
     * @param  array<string, mixed>  $cells
     * @param  array<string, mixed>  $periods
     */
    private function putSummary(
        array &$cells,
        array $periods,
        string $startColumn,
        DateTimeInterface $asOf,
        string $indicatorType
    ): void {
        $summary = $this->summaryValues($periods, (int) $asOf->format('n'), $indicatorType);
        $start = $this->columnNumber($startColumn);

        foreach ($summary as $offset => $value) {
            $cells[$this->columnName($start + $offset)] = $value;
        }
    }

    /**
     * @param  array<string, mixed>  $periods
     * @return array{0:float,1:float,2:float}
     */
    private function summaryValues(array $periods, int $month, string $indicatorType): array
    {
        $monthlyValues = array_map(
            fn (string $key): float => $this->numberValue($periods[$key] ?? 0),
            self::MONTH_KEYS
        );
        $type = $this->normalizeIndicatorType($indicatorType);
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
        $quarterIndex = intdiv($month - 1, 3);
        $quarter = $quarterValues[$quarterIndex];
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

    private function normalizeIndicatorType(string $type): string
    {
        $normalized = preg_replace('/[^a-z]+/', '', strtolower($type)) ?? '';

        return match (true) {
            str_starts_with($normalized, 'non') => 'non-cumulative',
            str_starts_with($normalized, 'semi') => 'semi-cumulative',
            default => 'cumulative',
        };
    }

    private function numberValue(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function inlineCell(string $reference, string $value, int $style): string
    {
        return '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'
            .$this->escape($value).'</t></is></c>';
    }

    private function numberCell(string $reference, float|int $value, int $style): string
    {
        $number = rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');

        return '<c r="'.$reference.'" s="'.$style.'"><v>'.($number === '-0' || $number === '' ? '0' : $number).'</v></c>';
    }

    private function columns(): string
    {
        $wide = [1 => 42, 2 => 42, 3 => 20];
        $xml = '<cols>';
        for ($column = 1; $column <= $this->columnNumber(self::LAST_COLUMN); $column++) {
            $width = $wide[$column] ?? 15;
            $xml .= '<col min="'.$column.'" max="'.$column.'" width="'.$width.'" customWidth="1"/>';
        }

        return $xml.'</cols>';
    }

    /** @return array<int, string> */
    private function merges(): array
    {
        return [
            'A1:O1', 'A2:O2', 'A3:O3', 'A5:O5', 'A6:O6',
            'A7:A8', 'B7:B8', 'C7:C8',
            'D7:F7', 'G7:I7', 'J7:L7', 'M7:O7',
        ];
    }

    private function columnNumber(string $column): int
    {
        $number = 0;
        foreach (str_split($column) as $character) {
            $number = ($number * 26) + ord($character) - 64;
        }

        return $number;
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'</Types>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private function workbook(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<workbookPr date1904="0" defaultThemeVersion="164011"/>'
            .'<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="24000" windowHeight="14000" activeTab="0" firstSheet="0"/></bookViews>'
            .'<sheets><sheet name="'.$this->escape($sheetName).'" sheetId="1" state="visible" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function appProperties(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            .'<Application>PMS</Application><DocSecurity>0</DocSecurity><ScaleCrop>false</ScaleCrop>'
            .'<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>1</vt:i4></vt:variant></vt:vector></HeadingPairs>'
            .'<TitlesOfParts><vt:vector size="1" baseType="lpstr"><vt:lpstr>'.$this->escape($sheetName).'</vt:lpstr></vt:vector></TitlesOfParts>'
            .'</Properties>';
    }

    private function coreProperties(): string
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            .'<dc:creator>PMS</dc:creator><cp:lastModifiedBy>PMS</cp:lastModifiedBy>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.$timestamp.'</dcterms:created>'
            .'<dcterms:modified xsi:type="dcterms:W3CDTF">'.$timestamp.'</dcterms:modified>'
            .'</cp:coreProperties>';
    }

    private function styles(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="1"><numFmt numFmtId="164" formatCode="#,##0.##;[Red]-#,##0.##;0"/></numFmts>
  <fonts count="9">
    <font><sz val="9"/><name val="Arial"/></font>
    <font><b/><sz val="14"/><name val="Arial"/></font>
    <font><i/><sz val="10"/><name val="Arial"/></font>
    <font><b/><color rgb="FFFFFFFF"/><sz val="9"/><name val="Arial"/></font>
    <font><b/><sz val="9"/><name val="Arial"/></font>
    <font><b/><color rgb="FF065F46"/><sz val="9"/><name val="Arial"/></font>
    <font><b/><color rgb="FF1E3A8A"/><sz val="9"/><name val="Arial"/></font>
    <font><b/><color rgb="FF92400E"/><sz val="9"/><name val="Arial"/></font>
    <font><b/><color rgb="FF5B21B6"/><sz val="9"/><name val="Arial"/></font>
  </fonts>
  <fills count="8">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1F4E78"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFC9F7DF"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFDBEAFE"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFEF3C7"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFEDE9FE"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF3F8F4"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border><left style="thin"><color rgb="FF808080"/></left><right style="thin"><color rgb="FF808080"/></right><top style="thin"><color rgb="FF808080"/></top><bottom style="thin"><color rgb="FF808080"/></bottom><diagonal/></border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="14">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>
    <xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="5" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="6" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="7" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="8" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>
    <xf numFmtId="0" fontId="4" fillId="7" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>
    <xf numFmtId="164" fontId="4" fillId="7" borderId="1" xfId="0" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }
}
