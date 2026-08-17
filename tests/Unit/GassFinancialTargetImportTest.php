<?php

namespace Tests\Unit;

use App\Http\Controllers\PhysicalExcelUploadController;
use App\Support\SimpleXlsxReader;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class GassFinancialTargetImportTest extends TestCase
{
    public function test_gass_columns_are_discovered_from_excel_headers_instead_of_fixed_letters(): void
    {
        $controller = (new PhysicalExcelUploadController)->forSector('gass');
        $configure = new ReflectionMethod(PhysicalExcelUploadController::class, 'configureExcelColumnLayoutFromRows');
        $configure->invoke($controller, [
            4 => [
                'D' => 'PROGRAM/ACTIVITY/PROJECT',
                'E' => 'PERFORMANCE INDICATOR',
                'F' => 'LOCATION (Province)',
                'K' => 'FY 2027 PHYSICAL TARGET',
                'AA' => 'Grand Total',
                'AC' => 'FY 2027 FINANCIAL TARGET',
                'AS' => 'Grand Total',
            ],
            6 => [
                'K' => 'Jan', 'L' => 'Feb', 'M' => 'Mar', 'N' => 'Total',
                'O' => 'Apr', 'P' => 'May', 'Q' => 'Jun', 'R' => 'Total',
                'S' => 'Jul', 'T' => 'Aug', 'U' => 'Sep', 'V' => 'Total',
                'W' => 'Oct', 'X' => 'Nov', 'Y' => 'Dec', 'Z' => 'Total',
                'AC' => 'Jan', 'AD' => 'Feb', 'AE' => 'Mar', 'AF' => 'Total',
                'AG' => 'Apr', 'AH' => 'May', 'AI' => 'Jun', 'AJ' => 'Total',
                'AK' => 'Jul', 'AL' => 'Aug', 'AM' => 'Sep', 'AN' => 'Total',
                'AO' => 'Oct', 'AP' => 'Nov', 'AQ' => 'Dec', 'AR' => 'Total',
            ],
        ], 'Shifted GASS');

        $normalize = new ReflectionMethod(PhysicalExcelUploadController::class, 'normalizeExcelCoreColumns');
        $row = $normalize->invoke($controller, [
            'D' => 'GASS Program from Excel',
            'E' => 'GASS Indicator from Excel',
            'F' => 'ABRA',
            'K' => '2', 'N' => '6', 'AA' => '20',
            'AC' => '125.50', 'AF' => '300', 'AJ' => '400',
            'AN' => '500', 'AR' => '600', 'AS' => '1800',
        ]);
        $physicalMethod = new ReflectionMethod(PhysicalExcelUploadController::class, 'physicalValuesFromExcelRow');
        $financialMethod = new ReflectionMethod(PhysicalExcelUploadController::class, 'financialTargetValuesFromExcelRow');
        $physical = $physicalMethod->invoke($controller, $row, 'target');
        $financial = $financialMethod->invoke($controller, $row);

        $this->assertSame('GASS Program from Excel', $row['A']);
        $this->assertSame('GASS Indicator from Excel', $row['B']);
        $this->assertSame('ABRA', $row['C']);
        $this->assertSame(2.0, $physical['jan']);
        $this->assertSame(20.0, $physical['annual_total']);
        $this->assertSame(125.5, $financial['jan']);
        $this->assertSame(300.0, $financial['q1']);
        $this->assertSame(1800.0, $financial['annual_total']);
    }

    public function test_financial_target_columns_map_to_database_periods(): void
    {
        $values = $this->financialValues([
            'AB' => '1,100.50',
            'AC' => '200',
            'AD' => '300',
            'AE' => '1600.50',
            'AF' => '400',
            'AG' => '500',
            'AH' => '600',
            'AI' => '1500',
            'AJ' => '700',
            'AK' => '800',
            'AL' => '900',
            'AM' => '2400',
            'AN' => '1000',
            'AO' => '1100',
            'AP' => '1200',
            'AQ' => '3300',
            'AR' => '8800.50',
        ]);

        $this->assertSame(1100.5, $values['jan']);
        $this->assertSame(1600.5, $values['q1']);
        $this->assertSame(1500.0, $values['q2']);
        $this->assertSame(2400.0, $values['q3']);
        $this->assertSame(3300.0, $values['q4']);
        $this->assertSame(8800.5, $values['annual_total']);
    }

    public function test_excel_errors_become_zero_and_annual_falls_back_to_quarters(): void
    {
        $values = $this->financialValues([
            'AB' => '#REF!',
            'AE' => '10',
            'AI' => '20',
            'AM' => '30',
            'AQ' => '40',
            'AR' => '#REF!',
        ]);

        $this->assertSame(0.0, $values['jan']);
        $this->assertSame(100.0, $values['annual_total']);
    }

    public function test_financial_accomplishment_columns_map_to_database_periods(): void
    {
        $values = $this->financialAccomplishmentValues([
            'BN' => '1,100.50',
            'BO' => '200',
            'BP' => '300',
            'BQ' => '1600.50',
            'BR' => '400',
            'BS' => '500',
            'BT' => '600',
            'BU' => '1500',
            'BV' => '700',
            'BW' => '800',
            'BX' => '900',
            'BY' => '2400',
            'BZ' => '1000',
            'CA' => '1100',
            'CB' => '1200',
            'CC' => '3300',
            'CD' => '8800.50',
        ]);

        $this->assertSame(1100.5, $values['jan']);
        $this->assertSame(1600.5, $values['q1']);
        $this->assertSame(1500.0, $values['q2']);
        $this->assertSame(2400.0, $values['q3']);
        $this->assertSame(3300.0, $values['q4']);
        $this->assertSame(8800.5, $values['annual_total']);
    }

    public function test_financial_accomplishment_annual_falls_back_to_quarters(): void
    {
        $values = $this->financialAccomplishmentValues([
            'BN' => '#REF!',
            'BQ' => '10',
            'BU' => '20',
            'BY' => '30',
            'CC' => '40',
            'CD' => '#REF!',
        ]);

        $this->assertSame(0.0, $values['jan']);
        $this->assertSame(100.0, $values['annual_total']);
    }

    private function financialValues(array $row): array
    {
        $controller = $this->controllerWithOfficialLayout();
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'financialTargetValuesFromExcelRow');

        return $method->invoke($controller, $row);
    }

    private function financialAccomplishmentValues(array $row): array
    {
        $controller = $this->controllerWithOfficialLayout();
        $method = new ReflectionMethod(PhysicalExcelUploadController::class, 'financialAccomplishmentValuesFromExcelRow');

        return $method->invoke($controller, $row);
    }

    private function controllerWithOfficialLayout(): PhysicalExcelUploadController
    {
        $controller = (new PhysicalExcelUploadController)->forSector('gass');
        $configure = new ReflectionMethod(PhysicalExcelUploadController::class, 'configureExcelColumnLayout');
        $configure->invoke(
            $controller,
            new SimpleXlsxReader,
            dirname(__DIR__, 2).'/resources/templates/DENR-CAR-2026-WFP-GAA-MIP.xlsx',
            'STO'
        );

        return $controller;
    }
}
