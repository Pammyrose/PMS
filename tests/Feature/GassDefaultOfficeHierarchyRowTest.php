<?php

namespace Tests\Feature;

use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class GassDefaultOfficeHierarchyRowTest extends TestCase
{
    public function test_default_office_row_lists_car_ro_and_the_six_provinces(): void
    {
        $html = (string) $this->view('components.default_office_unit_row', [
            'programCoreKey' => 'general management',
            'programSearchText' => 'General Management',
            'errors' => new ViewErrorBag,
        ]);

        $this->assertStringContainsString('class="data-row default-office-unit-row"', $html);
        $this->assertSame(8, substr_count($html, 'data-default-office-unit='));

        $expectedOrder = ['CAR', 'RO', 'ABRA', 'APAYAO', 'BENGUET', 'IFUGAO', 'KALINGA', 'MT.PROVINCE'];
        preg_match_all('/<div class="office-line[^"]*"[^>]*>\s*([^<]+?)\s*<\/div>/', $html, $matches);
        $renderedOfficeUnits = array_map('trim', $matches[1] ?? []);

        $this->assertSame($expectedOrder, $renderedOfficeUnits);
    }

    public function test_default_office_row_is_directly_after_each_gass_program_header(): void
    {
        foreach (['admin', 'regional', 'users', 'penro'] as $role) {
            $source = file_get_contents(resource_path("views/{$role}/gass/partials/gass_physical_table_rows.blade.php"));
            $includePosition = strpos($source, "@include('components.default_office_unit_row'");
            $groupingPosition = strpos($source, '$subActivityGroups =');

            $this->assertNotFalse($includePosition, "Missing default row for {$role}.");
            $this->assertNotFalse($groupingPosition, "Missing activity grouping for {$role}.");
            $this->assertLessThan($groupingPosition, $includePosition, "Default row must precede the green activity row for {$role}.");
        }
    }
}
