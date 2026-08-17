<?php

namespace Tests\Unit;

use App\Support\OfficialWfpTemplateWriter;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class OfficialWfpTemplateWriterTest extends TestCase
{
    public function test_official_layout_collapses_months_into_summary_columns(): void
    {
        $titleCells = '';
        $cells = '';
        $sectionStarts = ['I' => 5, 'AB' => 6, 'AU' => 7, 'BN' => 8];
        foreach (['I', 'J', 'K', 'AB', 'AC', 'AD', 'AU', 'AV', 'AW', 'BN', 'BO', 'BP'] as $column) {
            $style = $sectionStarts[$column] ?? 1;
            $titleCells .= '<c r="'.$column.'7" s="'.$style.'"'.(isset($sectionStarts[$column]) ? ' t="inlineStr"><is><t>Old title</t></is></c>' : '/>');
            $cells .= '<c r="'.$column.'8" s="'.$style.'"/><c r="'.$column.'9" s="1" t="s"><v>1</v></c>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<cols><col min="1" max="82" width="9" customWidth="1"/></cols>'
            .'<sheetData><row r="7">'.$titleCells.'</row><row r="8">'.$cells.'</row><row r="9"/></sheetData>'
            .'<mergeCells count="4">'
            .'<mergeCell ref="I7:X7"/><mergeCell ref="I8:L8"/>'
            .'<mergeCell ref="AB7:AQ7"/><mergeCell ref="BN7:CC7"/>'
            .'</mergeCells></worksheet>';

        $method = new ReflectionMethod(OfficialWfpTemplateWriter::class, 'transformSummaryLayout');
        $result = $method->invoke(
            new OfficialWfpTemplateWriter,
            $xml,
            new DateTimeImmutable('2026-07-22')
        );

        $this->assertIsString($result);
        $this->assertNotFalse(simplexml_load_string($result));
        $this->assertStringContainsString('r="I8" s="5" t="inlineStr"><is><t xml:space="preserve">ANNUAL', $result);
        $this->assertStringContainsString('r="J8" s="5" t="inlineStr"><is><t xml:space="preserve">Q3', $result);
        $this->assertStringContainsString('r="K8" s="5" t="inlineStr"><is><t xml:space="preserve">TO DATE', $result);
        $this->assertStringContainsString('<col min="12" max="12" width="9" customWidth="1" hidden="1"/>', $result);
        $this->assertStringContainsString('<col min="46" max="46" width="9" customWidth="1" hidden="1"/>', $result);
        $this->assertStringContainsString('<mergeCell ref="I7:K7"/>', $result);
        $this->assertStringContainsString('<mergeCell ref="BN7:BP7"/>', $result);
        $this->assertStringNotContainsString('<mergeCell ref="I8:L8"/>', $result);
    }

    public function test_sto_shifted_physical_accomplishment_header_uses_the_colored_source_style(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<cols><col min="1" max="82" width="9" customWidth="1"/></cols>'
            .'<sheetData>'
            .'<row r="7"><c r="AU7" s="515"/><c r="AV7" s="972" t="inlineStr"><is><t>FY2026 PHYSICAL ACCOMPLISHMENT</t></is></c><c r="AW7" s="973"/></row>'
            .'<row r="8"><c r="AU8" s="515"/><c r="AV8" s="987" t="inlineStr"><is><t>1st Quarter</t></is></c><c r="AW8" s="973"/></row>'
            .'<row r="9"><c r="AU9"/><c r="AV9"/><c r="AW9"/></row>'
            .'</sheetData><mergeCells count="2"><mergeCell ref="AV7:BK7"/><mergeCell ref="AV8:AY8"/></mergeCells>'
            .'</worksheet>';

        $method = new ReflectionMethod(OfficialWfpTemplateWriter::class, 'transformSummaryLayout');
        $result = $method->invoke(new OfficialWfpTemplateWriter, $xml, new DateTimeImmutable('2026-07-22'));

        $this->assertIsString($result);
        $this->assertStringContainsString('r="AU7" s="972" t="inlineStr"><is><t xml:space="preserve">FY2026 PHYSICAL ACCOMPLISHMENT', $result);
        $this->assertStringContainsString('r="AU8" s="987" t="inlineStr"><is><t xml:space="preserve">ANNUAL', $result);
        $this->assertStringContainsString('r="AV8" s="987" t="inlineStr"><is><t xml:space="preserve">Q3', $result);
        $this->assertStringContainsString('r="AW8" s="987" t="inlineStr"><is><t xml:space="preserve">TO DATE', $result);
        $this->assertStringContainsString('<mergeCell ref="AU7:AW7"/>', $result);
    }

    public function test_official_summary_ignores_stale_saved_annual_totals(): void
    {
        $method = new ReflectionMethod(OfficialWfpTemplateWriter::class, 'summaryValues');
        $summary = $method->invoke(
            new OfficialWfpTemplateWriter,
            ['annual_total' => 50, 'q3' => 50],
            7,
            'cumulative'
        );

        $this->assertSame([0.0, 0.0, 0.0], $summary);
    }

    public function test_penro_layout_hides_offices_outside_its_service_area(): void
    {
        $writer = new OfficialWfpTemplateWriter;
        $rowsMethod = new ReflectionMethod(OfficialWfpTemplateWriter::class, 'officeRowScope');
        $scope = $rowsMethod->invoke($writer, [
            9 => ['A' => 'Program heading', 'B' => 'Performance indicator', 'C' => 'CAR'],
            10 => ['C' => 'ABRA'],
            11 => ['C' => 'PENRO'],
            12 => ['C' => 'BANGUED'],
            13 => ['C' => 'LAGANGILANG'],
            14 => ['C' => 'APAYAO'],
            15 => ['C' => 'PENRO'],
            16 => ['C' => 'CALANASAN'],
            17 => ['C' => 'CONNER'],
            18 => ['C' => 'N/A'],
        ], ['ABRA', 'BANGUED', 'LAGANGILANG']);

        $this->assertSame([14, 15, 16, 17], $scope['hidden']);
        $this->assertSame([9], $scope['clear_location']);

        $hideMethod = new ReflectionMethod(OfficialWfpTemplateWriter::class, 'hideRows');
        $xml = '<worksheet><sheetData>'
            .'<row r="10"><c r="C10"/></row><row r="14"><c r="C14"/></row>'
            .'<row r="15" hidden="0"><c r="C15"/></row><row r="18"><c r="C18"/></row>'
            .'</sheetData></worksheet>';
        $result = $hideMethod->invoke($writer, $xml, $scope['hidden']);

        $this->assertStringContainsString('<row r="10">', $result);
        $this->assertStringContainsString('<row r="14" hidden="1">', $result);
        $this->assertStringContainsString('<row r="15" hidden="1">', $result);
        $this->assertStringContainsString('<row r="18">', $result);
    }
}
