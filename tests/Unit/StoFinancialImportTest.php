<?php

namespace Tests\Unit;

use App\Http\Controllers\PhysicalExcelUploadController;
use App\Support\SimpleXlsxReader;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class StoFinancialImportTest extends TestCase
{
    public function test_sto_columns_are_discovered_from_excel_headers_instead_of_fixed_letters(): void
    {
        $controller = (new PhysicalExcelUploadController)->forSector('sto');
        $configure = new ReflectionMethod(PhysicalExcelUploadController::class, 'configureExcelColumnLayoutFromRows');
        $configure->invoke($controller, [
            4 => [
                'D' => 'PROGRAM/ACTIVITY/PROJECT',
                'E' => 'PERFORMANCE INDICATOR',
                'F' => 'LOCATION (Province)',
                'K' => 'FY 2027 PHYSICAL TARGET',
                'AA' => 'Grand Total',
            ],
            6 => [
                'K' => 'Jan', 'L' => 'Feb', 'M' => 'Mar', 'N' => 'Total',
                'O' => 'Apr', 'P' => 'May', 'Q' => 'Jun', 'R' => 'Total',
                'S' => 'Jul', 'T' => 'Aug', 'U' => 'Sep', 'V' => 'Total',
                'W' => 'Oct', 'X' => 'Nov', 'Y' => 'Dec', 'Z' => 'Total',
            ],
        ], 'Shifted STO');

        $normalize = new ReflectionMethod(PhysicalExcelUploadController::class, 'normalizeExcelCoreColumns');
        $row = $normalize->invoke($controller, [
            'D' => 'Program from Excel',
            'E' => 'Indicator from Excel',
            'F' => 'ABRA',
            'K' => '1', 'N' => '6', 'R' => '7', 'V' => '8', 'Z' => '9', 'AA' => '30',
        ]);
        $valuesMethod = new ReflectionMethod(PhysicalExcelUploadController::class, 'physicalValuesFromExcelRow');
        $values = $valuesMethod->invoke($controller, $row, 'target');

        $this->assertSame('Program from Excel', $row['A']);
        $this->assertSame('Indicator from Excel', $row['B']);
        $this->assertSame('ABRA', $row['C']);
        $this->assertSame(1.0, $values['jan']);
        $this->assertSame(6.0, $values['q1']);
        $this->assertSame(30.0, $values['annual_total']);
    }

    public function test_sto_financial_target_columns_map_to_database_periods(): void
    {
        $values = $this->invoke('financialTargetValuesFromExcelRow', [[
            'AB' => '1,100.50',
            'AE' => '1600.50',
            'AI' => '1500',
            'AM' => '2400',
            'AQ' => '3300',
            'AR' => '8800.50',
        ]]);

        $this->assertSame(1100.5, $values['jan']);
        $this->assertSame(1600.5, $values['q1']);
        $this->assertSame(1500.0, $values['q2']);
        $this->assertSame(2400.0, $values['q3']);
        $this->assertSame(3300.0, $values['q4']);
        $this->assertSame(8800.5, $values['annual_total']);
    }

    public function test_sto_financial_accomplishment_columns_map_and_fall_back_to_quarters(): void
    {
        $values = $this->invoke('financialAccomplishmentValuesFromExcelRow', [[
            'BN' => '#REF!',
            'BQ' => '10',
            'BU' => '20',
            'BY' => '30',
            'CC' => '40',
            'CD' => '#REF!',
        ]]);

        $this->assertSame(0.0, $values['jan']);
        $this->assertSame(10.0, $values['q1']);
        $this->assertSame(20.0, $values['q2']);
        $this->assertSame(30.0, $values['q3']);
        $this->assertSame(40.0, $values['q4']);
        $this->assertSame(100.0, $values['annual_total']);
    }

    public function test_official_sto_sheet_contains_both_financial_import_sections(): void
    {
        $template = dirname(__DIR__, 2).'/resources/templates/DENR-CAR-2026-WFP-GAA-MIP.xlsx';
        $reader = new SimpleXlsxReader;

        $this->assertTrue($this->invoke('hasFinancialTargetColumns', [$reader, $template, 'STO']));
        $this->assertTrue($this->invoke('hasFinancialAccomplishmentColumns', [$reader, $template, 'STO']));
    }

    public function test_default_excel_upload_imports_both_physical_sections(): void
    {
        $template = dirname(__DIR__, 2).'/resources/templates/DENR-CAR-2026-WFP-GAA-MIP.xlsx';
        $controller = (new PhysicalExcelUploadController)->forSector('sto');
        $resolveType = new ReflectionMethod(PhysicalExcelUploadController::class, 'resolvePhysicalImportType');
        $importTypes = new ReflectionMethod(PhysicalExcelUploadController::class, 'physicalImportTypes');

        $resolvedType = $resolveType->invoke($controller, $template, 'STO', null);

        $this->assertSame('both', $resolvedType);
        $this->assertSame(
            ['target', 'accomplishment'],
            $importTypes->invoke($controller, $resolvedType)
        );
    }

    public function test_combined_physical_import_result_reports_each_dataset_separately(): void
    {
        $controller = (new PhysicalExcelUploadController)->forSector('sto');
        $combine = new ReflectionMethod(PhysicalExcelUploadController::class, 'combinePhysicalImportResults');
        $result = $combine->invoke($controller, [
            'target' => [
                'imported' => 8,
                'financial_imported' => 8,
                'financial_accomplishment_imported' => 8,
                'skipped' => 2,
                'placeholders' => 1,
            ],
            'accomplishment' => [
                'imported' => 6,
                'financial_imported' => 0,
                'financial_accomplishment_imported' => 0,
                'skipped' => 3,
                'placeholders' => 0,
            ],
        ]);

        $this->assertSame(14, $result['imported']);
        $this->assertSame(8, $result['target_imported']);
        $this->assertSame(6, $result['accomplishment_imported']);
        $this->assertSame(8, $result['financial_imported']);
        $this->assertSame(8, $result['financial_accomplishment_imported']);
        $this->assertSame(5, $result['skipped']);
    }

    public function test_all_physical_sector_importers_share_the_financial_column_mappings(): void
    {
        $sectors = ['enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing'];

        foreach ($sectors as $sector) {
            $target = $this->invokeOn($sector, 'financialTargetValuesFromExcelRow', [[
                'AB' => '125',
                'AM' => '75',
                'AR' => '200',
            ]]);
            $accomplishment = $this->invokeOn($sector, 'financialAccomplishmentValuesFromExcelRow', [[
                'BN' => '25',
                'BY' => '15',
                'CD' => '40',
            ]]);

            $this->assertSame(125.0, $target['jan'], $sector);
            $this->assertSame(75.0, $target['q3'], $sector);
            $this->assertSame(200.0, $target['annual_total'], $sector);
            $this->assertSame(25.0, $accomplishment['jan'], $sector);
            $this->assertSame(15.0, $accomplishment['q3'], $sector);
            $this->assertSame(40.0, $accomplishment['annual_total'], $sector);
        }
    }

    private function invoke(string $methodName, array $arguments): mixed
    {
        return $this->invokeOn('sto', $methodName, $arguments);
    }

    private function invokeOn(string $sector, string $methodName, array $arguments): mixed
    {
        $controller = (new PhysicalExcelUploadController)->forSector($sector);
        $configure = new ReflectionMethod(PhysicalExcelUploadController::class, 'configureExcelColumnLayout');
        $configure->invoke(
            $controller,
            new SimpleXlsxReader,
            dirname(__DIR__, 2).'/resources/templates/DENR-CAR-2026-WFP-GAA-MIP.xlsx',
            'STO'
        );
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, $methodName);

        return $method->invokeArgs($controller, $arguments);
    }
}
