<?php

namespace Tests\Unit;

use App\Support\OfficialWfpTemplateWriter;
use App\Support\PhysicalPerformanceSectionFormatter;
use App\Support\SimpleXlsxReader;
use App\Support\SimpleXlsxWriter;
use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class PhysicalPerformanceSectionFormatterTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pms-physical-section-'.bin2hex(random_bytes(6)).'.xlsx';
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
        parent::tearDown();
    }

    public function test_it_changes_only_the_performance_section_and_keeps_identity_cells(): void
    {
        (new SimpleXlsxWriter)->writeWfp($this->path, 'GASS', 'GASS SECTOR', 2025, [[
            'pap' => 'ADMINISTRATIVE SERVICES',
            'indicator' => 'One (1) Updated Annual Procurement Plan',
            'indicator_type' => 'Cumulative',
            'office' => 'CAR',
            'physical_target' => ['jan' => 10, 'apr' => 20, 'jul' => 84, 'oct' => 6],
            'physical_accomplishment' => ['jan' => 10, 'apr' => 20, 'jul' => 84],
            'financial_target' => ['jan' => 100, 'apr' => 200, 'jul' => 700],
            'financial_accomplishment' => ['jan' => 50, 'apr' => 100, 'jul' => 500],
        ], [
            'pap' => 'ADMINISTRATIVE SERVICES',
            'indicator' => 'One (1) Updated Annual Procurement Plan',
            'indicator_type' => 'Cumulative',
            'office' => 'ABRA',
            'physical_target' => ['jan' => 4],
            'financial_target' => ['jan' => 40],
            'financial_accomplishment' => ['jan' => 20],
        ], [
            'pap' => 'ADMINISTRATIVE SERVICES',
            'indicator' => 'One (1) Updated Annual Procurement Plan',
            'indicator_type' => 'Cumulative',
            'office' => 'BANGUED',
            'physical_target' => ['jan' => 2],
            'financial_target' => ['jan' => 20],
            'financial_accomplishment' => ['jan' => 10],
        ]], new DateTimeImmutable('2025-09-01'));
        $identityBefore = $this->identitySnapshot($this->path, ['A7', 'B7', 'C7', 'A10', 'B10', 'C10']);

        (new PhysicalPerformanceSectionFormatter)->format(
            $this->path,
            'GASS',
            ['D', 'E', 'F'],
            ['J', 'K', 'L'],
            10,
            ['G', 'H', 'I'],
            ['M', 'N', 'O']
        );
        $this->assertSame(
            $identityBefore,
            $this->identitySnapshot($this->path, ['A7', 'B7', 'C7', 'A10', 'B10', 'C10'])
        );

        $rows = iterator_to_array((new SimpleXlsxReader)->rows($this->path, 'GASS'));
        $this->assertSame('Programs/Activities/Projects (P/A/Ps)', $rows[7]['A']);
        $this->assertSame('PERFORMANCE INDICATOR', $rows[7]['B']);
        $this->assertSame('OFFICE', $rows[7]['C']);
        $this->assertSame('PHYSICAL PERFORMANCE', $rows[7]['D']);
        $this->assertSame('Target', $rows[8]['D']);
        $this->assertSame('Accomp', $rows[8]['G']);
        $this->assertSame('%Accomp', $rows[8]['I']);
        $this->assertSame('ADMINISTRATIVE SERVICES', $rows[10]['A']);
        $this->assertSame('One (1) Updated Annual Procurement Plan', $rows[10]['B']);
        $this->assertSame('CAR', $rows[10]['C']);
        $this->assertSame('120', $rows[10]['D']);
        $this->assertSame('84', $rows[10]['E']);
        $this->assertSame('114', $rows[10]['F']);
        $this->assertSame('84', $rows[10]['G']);
        $this->assertSame('114', $rows[10]['H']);
        $this->assertSame('1', $rows[10]['I']);
        $this->assertSame('0.95', $rows[10]['J']);
        $this->assertSame('FINANCIAL PERFORMANCE', $rows[7]['K']);
        $this->assertSame('Obligation', $rows[8]['K']);
        $this->assertSame('Disbursement', $rows[8]['N']);
        $this->assertSame('% Budget Utilization Rate (BUR)', $rows[8]['P']);
        $this->assertSame('Allotment', $rows[9]['K']);
        $this->assertSame('This Quarter', $rows[9]['L']);
        $this->assertSame('To Date', $rows[9]['M']);
        $this->assertSame('1000', $rows[10]['K']);
        $this->assertSame('700', $rows[10]['L']);
        $this->assertSame('1000', $rows[10]['M']);
        $this->assertSame('500', $rows[10]['N']);
        $this->assertSame('650', $rows[10]['O']);
        $this->assertSame('1', $rows[10]['P']);
        $this->assertSame('0.65', $rows[10]['Q']);
        $this->assertSame('0.65', $rows[10]['R']);
        $this->assertSame('ABRA', $rows[11]['C']);
        $this->assertSame('40', $rows[11]['K']);
        $this->assertSame('BANGUED', $rows[12]['C']);
        $this->assertSame('2', $rows[12]['D']);
        foreach (range('K', 'R') as $column) {
            $this->assertArrayNotHasKey($column, $rows[12]);
        }

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($this->path) === true);
        $worksheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $styles = (string) $zip->getFromName('xl/styles.xml');
        $zip->close();

        $this->assertStringContainsString('<mergeCell ref="D7:J7"/>', $worksheet);
        $this->assertStringContainsString('<mergeCell ref="K7:R7"/>', $worksheet);
        $this->assertStringContainsString('<mergeCell ref="K8:M8"/>', $worksheet);
        $this->assertStringNotContainsString('<mergeCell ref="K8:K9"/>', $worksheet);
        $this->assertStringContainsString('<mergeCell ref="N8:O8"/>', $worksheet);
        $this->assertStringContainsString('<mergeCell ref="P8:R8"/>', $worksheet);
        $this->assertStringContainsString('formatCode="0.00%"', $styles);
    }

    public function test_it_preserves_official_template_identity_rows(): void
    {
        $template = dirname(__DIR__, 2).'/resources/templates/DENR-CAR-2026-WFP-GAA-MIP.xlsx';
        (new OfficialWfpTemplateWriter)->write(
            $template,
            $this->path,
            'GASS',
            [],
            [],
            new DateTimeImmutable('2025-09-01')
        );
        $identityBefore = $this->identitySnapshot($this->path, ['A7', 'B7', 'C7', 'A11', 'B11', 'C11']);

        (new PhysicalPerformanceSectionFormatter)->format(
            $this->path,
            'GASS',
            ['I', 'J', 'K'],
            ['AU', 'AV', 'AW'],
            11,
            ['AB', 'AC', 'AD'],
            ['BN', 'BO', 'BP']
        );
        $this->assertSame(
            $identityBefore,
            $this->identitySnapshot($this->path, ['A7', 'B7', 'C7', 'A11', 'B11', 'C11'])
        );

        $rows = iterator_to_array((new SimpleXlsxReader)->rows($this->path, 'GASS'));
        $this->assertSame('PROGRAM/ACTIVITY/PROJECT', $rows[7]['A']);
        $this->assertSame('PERFORMANCE INDICATOR', $rows[7]['B']);
        $this->assertSame('LOCATION (Province)', $rows[7]['C']);
        $this->assertSame('PHYSICAL PERFORMANCE', $rows[7]['D']);
        $this->assertSame('FINANCIAL PERFORMANCE', $rows[7]['K']);
        $this->assertSame('Obligation', $rows[8]['K']);
        $this->assertSame('Disbursement', $rows[8]['N']);
        $this->assertSame('% Budget Utilization Rate (BUR)', $rows[8]['P']);
        $this->assertSame('Allotment', $rows[9]['K']);
        $this->assertSame('GENERAL ADMINISTRATION AND SUPPORT', $rows[11]['A']);
        $this->assertSame('SERVICES (GASS)', $rows[12]['A']);
    }

    /** @param array<int, string> $references */
    private function identitySnapshot(string $path, array $references): array
    {
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $xml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        $document = new DOMDocument;
        $this->assertTrue($document->loadXML($xml));
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $snapshot = [];
        foreach ($references as $reference) {
            $cell = $xpath->query("//x:c[@r='{$reference}']")?->item(0);
            $this->assertInstanceOf(DOMElement::class, $cell);
            $snapshot[$reference] = [
                'style' => $cell->getAttribute('s'),
                'type' => $cell->getAttribute('t'),
                'content' => $cell->textContent,
            ];
        }

        foreach ([1, 2, 3] as $columnNumber) {
            $column = $xpath->query("//x:cols/x:col[@min <= '{$columnNumber}' and @max >= '{$columnNumber}']")?->item(0);
            $this->assertInstanceOf(DOMElement::class, $column);
            $snapshot['column_'.$columnNumber] = $column->getAttribute('width');
        }
        $identityMerges = [];
        foreach ($xpath->query('//x:mergeCells/x:mergeCell') ?: [] as $merge) {
            if (! $merge instanceof DOMElement) {
                continue;
            }
            $reference = $merge->getAttribute('ref');
            if (preg_match('/^[A-C](?:[7-9]|[1-9][0-9]+):[A-C](?:[7-9]|[1-9][0-9]+)$/', $reference)) {
                $identityMerges[] = $reference;
            }
        }
        sort($identityMerges);
        $snapshot['identity_merges'] = $identityMerges;

        return $snapshot;
    }
}
