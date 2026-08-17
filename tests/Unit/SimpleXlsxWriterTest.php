<?php

namespace Tests\Unit;

use App\Support\SimpleXlsxReader;
use App\Support\SimpleXlsxWriter;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class SimpleXlsxWriterTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pms-wfp-test-'.bin2hex(random_bytes(6)).'.xlsx';
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
        parent::tearDown();
    }

    public function test_it_writes_the_wfp_summary_layout_and_calculated_values(): void
    {
        (new SimpleXlsxWriter)->writeWfp($this->path, 'GASS', 'GASS SECTOR', 2026, [[
            'pap' => "Program\nActivity",
            'indicator' => 'Number of outputs',
            'indicator_type' => 'Cumulative',
            'office' => 'CAR',
            'physical_target' => ['jan' => 10, 'q1' => 10, 'annual_total' => 10],
            'financial_target' => ['apr' => 250.5, 'q2' => 250.5, 'annual_total' => 250.5],
            'physical_accomplishment' => ['jul' => 7, 'q3' => 7, 'annual_total' => 7],
            'financial_accomplishment' => ['annual_total' => 50],
        ]], new DateTimeImmutable('2026-07-22'));

        $this->assertFileExists($this->path);

        $reader = new SimpleXlsxReader;
        $rows = iterator_to_array($reader->rows($this->path, 'GASS'));

        $this->assertSame('FY 2026 WORK AND FINANCIAL PLAN', $rows[2]['A']);
        $this->assertSame('Physical Target', $rows[7]['D']);
        $this->assertSame('Financial Target', $rows[7]['G']);
        $this->assertSame('Physical Accomplishment', $rows[7]['J']);
        $this->assertSame('Financial Accomplishment', $rows[7]['M']);
        $this->assertSame('ANNUAL', $rows[8]['D']);
        $this->assertSame('Q3', $rows[8]['E']);
        $this->assertSame('TO DATE', $rows[8]['F']);
        $this->assertSame('Number of outputs', $rows[10]['B']);
        $this->assertSame('10', $rows[10]['D']);
        $this->assertSame('0', $rows[10]['E']);
        $this->assertSame('10', $rows[10]['F']);
        $this->assertSame('250.5', $rows[10]['G']);
        $this->assertSame('0', $rows[10]['H']);
        $this->assertSame('250.5', $rows[10]['I']);
        $this->assertSame('7', $rows[10]['J']);
        $this->assertSame('7', $rows[10]['K']);
        $this->assertSame('7', $rows[10]['L']);
        $this->assertSame('0', $rows[10]['M']);
        $this->assertSame('0', $rows[10]['N']);
        $this->assertSame('0', $rows[10]['O']);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($this->path) === true);
        $worksheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $styles = (string) $zip->getFromName('xl/styles.xml');
        $workbook = (string) $zip->getFromName('xl/workbook.xml');
        $zip->close();

        $this->assertStringContainsString('orientation="landscape"', $worksheet);
        $this->assertStringContainsString('pane xSplit="3" ySplit="9"', $worksheet);
        $this->assertStringContainsString('tabSelected="1"', $worksheet);
        $this->assertStringContainsString('zoomScale="85"', $worksheet);
        $this->assertStringContainsString('mergeCell ref="D7:F7"', $worksheet);
        $this->assertStringContainsString('mergeCell ref="G7:I7"', $worksheet);
        $this->assertStringContainsString('mergeCell ref="J7:L7"', $worksheet);
        $this->assertStringContainsString('mergeCell ref="M7:O7"', $worksheet);
        $this->assertStringContainsString('r="D8" s="5"', $worksheet);
        $this->assertStringContainsString('r="E8" s="5"', $worksheet);
        $this->assertStringContainsString('r="F8" s="5"', $worksheet);
        $this->assertStringContainsString('r="G8" s="7"', $worksheet);
        $this->assertStringContainsString('r="H8" s="7"', $worksheet);
        $this->assertStringContainsString('r="I8" s="7"', $worksheet);
        $this->assertStringNotContainsString('<autoFilter', $worksheet);
        $this->assertStringContainsString('FFC9F7DF', $styles);
        $this->assertStringContainsString('FFDBEAFE', $styles);
        $this->assertStringContainsString('FFFEF3C7', $styles);
        $this->assertStringContainsString('FFEDE9FE', $styles);
        $this->assertStringContainsString('activeTab="0"', $workbook);
        $this->assertStringContainsString('state="visible"', $workbook);
        $this->assertStringNotContainsString('fullCalcOnLoad', $workbook);
    }
}
