<?php

namespace Tests\Feature;

use App\Http\Controllers\PhysicalExcelUploadController;
use App\Http\Controllers\WfpExcelExportController;
use App\Models\User;
use App\Support\SimpleXlsxReader;
use App\Support\SimpleXlsxWriter;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class WfpExcelExportRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_physical_excel_routes_use_the_single_sector_configured_controller(): void
    {
        foreach (['gass', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing'] as $sector) {
            foreach (['import_excel' => 'importExcel', 'import_excel.preview' => 'previewExcelImport'] as $routeSuffix => $method) {
                $route = Route::getRoutes()->getByName("admin.{$sector}_physical.{$routeSuffix}");

                $this->assertNotNull($route);
                $this->assertSame(PhysicalExcelUploadController::class.'@'.$method, $route->getActionName());
                $this->assertSame($sector, $route->defaults['sector'] ?? null);
            }
        }
    }

    public function test_each_additional_sector_uses_the_gass_style_import_preview(): void
    {
        $sectors = [
            'enf' => 'ENF',
            'pa' => 'PA',
            'engp' => 'ENGP',
            'lands' => 'LANDS',
            'soilcon' => 'SOILCON',
            'nra' => 'NRA',
            'paria' => 'PARIA',
            'cobb' => 'COBB',
            'continuing' => 'CONTINUING',
        ];

        foreach ($sectors as $sector => $label) {
            $this->view("components.{$sector}_excel_upload")
                ->assertSee("id=\"{$sector}ExcelPreviewModal\"", false)
                ->assertSee("{$sector}-preview-table", false)
                ->assertSee("{$sector}-preview-stat", false)
                ->assertSee("{$sector}-preview-level", false)
                ->assertSee(route("admin.{$sector}_physical.import_excel.preview"), false)
                ->assertSee('Sorting warnings')
                ->assertSee('CAR total')
                ->assertSee('office row(s)')
                ->assertSee("{$label} Excel preview error:");
        }
    }

    public function test_excel_export_requires_authentication(): void
    {
        foreach (['gass', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing'] as $sector) {
            $this->get("/wfp/export/{$sector}?year=2026")
                ->assertRedirect(route('login'));
        }
    }

    public function test_unknown_sector_does_not_match_the_export_route(): void
    {
        $this->get('/wfp/export/unknown?year=2026')
            ->assertNotFound();
    }

    public function test_gass_excel_uses_the_same_program_financial_totals_as_the_system_summary(): void
    {
        $rows = collect([
            ['office' => 'CAR', 'financial_target' => ['jan' => 999]],
            ['office' => 'RO', 'financial_target' => ['jan' => 10]],
            ['office' => 'ABRA', 'financial_target' => ['jan' => 20]],
            ['office' => 'APAYAO', 'financial_target' => ['jan' => 0]],
            ['office' => 'BANGUED', 'financial_target' => ['jan' => 30]],
        ])->map(fn (array $row): array => [
            'pap' => 'GENERAL MANAGEMENT\nA. REPAIR',
            'indicator' => 'Sample indicator',
            'physical_target' => ['jan' => 1],
            'physical_accomplishment' => [],
            'financial_accomplishment' => [],
            '_program_key' => 'general management',
            '_program_label' => 'GENERAL MANAGEMENT',
            ...$row,
        ])->all();

        $method = new ReflectionMethod(WfpExcelExportController::class, 'withSystemFinancialSummaryRows');
        $result = collect($method->invoke(new WfpExcelExportController, $rows));
        $summary = $result->where('is_financial_summary', true)->keyBy('office');

        $this->assertSame(60.0, $summary['CAR']['financial_target']['jan']);
        $this->assertSame(10.0, $summary['RO']['financial_target']['jan']);
        $this->assertSame(50.0, $summary['ABRA']['financial_target']['jan']);
        $this->assertSame([], $summary['APAYAO']['financial_target']);
        $this->assertCount(8, $summary);
        $this->assertTrue($result->where('is_financial_summary', true)->every(
            fn (array $row): bool => $row['physical_target'] === []
        ));
        $this->assertTrue($result->where('is_financial_summary', false)->every(
            fn (array $row): bool => $row['financial_target'] === []
        ));
    }

    public function test_gass_financial_values_stay_with_their_own_major_pap(): void
    {
        $controller = new WfpExcelExportController;
        $outputRow = new ReflectionMethod(WfpExcelExportController::class, 'outputRow');
        $summaryRows = new ReflectionMethod(WfpExcelExportController::class, 'withSystemFinancialSummaryRows');

        $rows = [
            $outputRow->invoke(
                $controller,
                "GENERAL MANAGEMENT\n1. FIRST MAJOR PAP\n1.1 ACTIVITY",
                'First indicator',
                'Cumulative',
                'RO',
                ['financial_target' => ['jan' => 10]]
            ),
            $outputRow->invoke(
                $controller,
                "GENERAL MANAGEMENT\n2. SECOND MAJOR PAP\n2.1 ACTIVITY",
                'Second indicator',
                'Cumulative',
                'ABRA',
                ['financial_target' => ['jan' => 20]]
            ),
        ];

        $result = collect($summaryRows->invoke($controller, $rows));
        $majorPapSummaries = $result
            ->where('is_financial_summary', true)
            ->groupBy('_program_key');

        $this->assertCount(2, $majorPapSummaries);
        $this->assertSame(
            [10.0, 20.0],
            $majorPapSummaries
                ->map(fn ($group) => (float) ($group->firstWhere('office', 'CAR')['financial_target']['jan'] ?? 0))
                ->sort()
                ->values()
                ->all()
        );
    }

    public function test_gass_ui_workbook_keeps_financial_values_off_indicator_rows(): void
    {
        $rows = collect([
            ['office' => 'RO', 'financial_target' => ['jan' => 10]],
            ['office' => 'ABRA', 'financial_target' => ['jan' => 20]],
        ])->map(fn (array $row): array => [
            'pap' => "HUMAN RESOURCE DEVELOPMENT\n3.1 SALN",
            'indicator' => 'Report of SALN Certification submitted',
            'indicator_type' => 'Cumulative',
            'physical_target' => ['jan' => 1],
            'physical_accomplishment' => [],
            'financial_accomplishment' => [],
            '_program_key' => 'human resource development',
            '_program_label' => 'HUMAN RESOURCE DEVELOPMENT',
            ...$row,
        ])->all();

        $method = new ReflectionMethod(WfpExcelExportController::class, 'withSystemFinancialSummaryRows');
        $exportRows = $method->invoke(new WfpExcelExportController, $rows);
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'pms-gass-ui-export-'.bin2hex(random_bytes(6)).'.xlsx';

        try {
            (new SimpleXlsxWriter)->writePerformanceReport(
                $path,
                'GASS',
                'GENERAL ADMINISTRATION AND SUPPORT SERVICES (GASS)',
                2026,
                $exportRows,
                new DateTimeImmutable('2026-10-01')
            );

            $sheetRows = collect(iterator_to_array((new SimpleXlsxReader)->rows($path, 'GASS', false)));
            $this->assertSame('PHYSICAL PERFORMANCE', $sheetRows[6]['D'] ?? null);
            $this->assertSame('Expense Class', $sheetRows[6]['K'] ?? null);
            $this->assertSame('FINANCIAL PERFORMANCE', $sheetRows[6]['L'] ?? null);
            $this->assertSame('REMARKS/Justifications', $sheetRows[6]['T'] ?? null);
            $this->assertSame('Allotment', $sheetRows[7]['L'] ?? null);
            $this->assertSame('Obligation', $sheetRows[7]['M'] ?? null);
            $this->assertSame('Disbursement', $sheetRows[7]['O'] ?? null);
            $this->assertSame('(Disb/ Oblig) *100', $sheetRows[8]['S'] ?? null);
            $summaryAbra = $sheetRows->first(
                fn (array $row): bool => ($row['B'] ?? '') === '' && ($row['C'] ?? '') === 'ABRA'
            );
            $summaryApayao = $sheetRows->first(
                fn (array $row): bool => ($row['B'] ?? '') === '' && ($row['C'] ?? '') === 'APAYAO'
            );
            $indicatorRows = $sheetRows->filter(
                fn (array $row): bool => ($row['B'] ?? '') === 'Report of SALN Certification submitted'
            );

            $this->assertSame('20', $summaryAbra['L'] ?? null);
            foreach (range('L', 'S') as $column) {
                $this->assertArrayNotHasKey($column, $summaryApayao);
            }
            $this->assertNotEmpty($indicatorRows);
            $this->assertTrue($indicatorRows->every(function (array $row): bool {
                foreach (range('K', 'S') as $column) {
                    if (array_key_exists($column, $row)) {
                        return false;
                    }
                }

                return true;
            }));

            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($path) === true);
            $worksheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
            $zip->close();
            $this->assertStringContainsString('<mergeCell ref="A15:A22"/>', $worksheet);
            $this->assertStringContainsString('<mergeCell ref="A23:A24"/>', $worksheet);
            $this->assertStringContainsString('<mergeCell ref="B23:B24"/>', $worksheet);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_all_sectors_use_the_ui_report_instead_of_the_prefilled_official_template(): void
    {
        $reflection = new \ReflectionClass(WfpExcelExportController::class);

        $this->assertSame([], $reflection->getConstant('OFFICIAL_TEMPLATE_SECTORS'));
    }

    public function test_user_sector_toolbars_render_excel_downloads_but_not_uploads(): void
    {
        foreach (['gass', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing'] as $sector) {
            $this->view("users.{$sector}.partials.{$sector}_physical_toolbar")
                ->assertDontSee('Upload Excel')
                ->assertSee('Generate Excel');
        }
    }

    public function test_every_view_role_can_generate_excel_exports(): void
    {
        DB::table('types')->insert(['code' => 'GASS', 'desc' => 'GASS']);
        foreach (['PROGRAM', 'PROJECT', 'MAIN ACTIVITY', 'SUB-ACTIVITY', 'SUB-SUB-ACTIVITY', 'SUB-SUB-SUB-ACTIVITY', 'LEVEL-7', 'LEVEL-8', 'LEVEL-9'] as $name) {
            DB::table('record_types')->insert(['name' => $name, 'desc' => $name]);
        }
        foreach (['super-admin', 'admin', 'ro-office', 'ro office', 'penro', 'user', 'cenro'] as $role) {
            $user = User::query()->create([
                'name' => strtoupper($role).' Export Test',
                'email' => str_replace(' ', '_', $role).'-export@example.test',
                'password' => 'Password1!',
                'role' => $role,
            ]);

            $this->actingAs($user)
                ->get('/wfp/export/gass?year=2026')
                ->assertOk();

            auth()->logout();
        }
    }

    public function test_every_sector_download_uses_its_own_heading_and_the_gass_report_grid(): void
    {
        $admin = User::query()->create([
            'name' => 'All-sector export tester',
            'email' => 'all-sector-export@example.test',
            'password' => 'Password1!',
            'role' => 'admin',
        ]);
        $this->actingAs($admin);
        foreach (['PROGRAM', 'PROJECT', 'MAIN ACTIVITY', 'SUB-ACTIVITY', 'SUB-SUB-ACTIVITY', 'SUB-SUB-SUB-ACTIVITY', 'LEVEL-7', 'LEVEL-8', 'LEVEL-9'] as $name) {
            DB::table('record_types')->insert(['name' => $name, 'desc' => $name]);
        }
        $sectors = [
            'gass' => ['GASS', 'GASS'],
            'sto' => ['STO', 'STO'],
            'enf' => ['ENF', 'NRE&RP'],
            'pa' => ['Biodiv', 'PA'],
            'engp' => ['ENGP', 'E-NGP'],
            'lands' => ['Lands', 'LANDS'],
            'soilcon' => ['Soilcon', 'SOILCON'],
            'nra' => ['NRA', 'NRA'],
            'paria' => ['PARIA', 'PARIA'],
            'cobb' => ['COBB', 'COBB'],
            'continuing' => ['CONTINUING', 'CONTINUING'],
        ];
        foreach ($sectors as [$type]) {
            DB::table('types')->insert(['code' => $type, 'desc' => $type]);
        }

        foreach ($sectors as $sector => [, $sheetName]) {
            $response = $this->get("/wfp/export/{$sector}?year=2026")->assertOk();
            $path = $response->baseResponse->getFile()->getPathname();
            try {
                $sheet = iterator_to_array((new SimpleXlsxReader)->rows($path, $sheetName, false));
                $this->assertSame('PHYSICAL PERFORMANCE', $sheet[6]['D'] ?? null, $sector);
                $this->assertSame('FINANCIAL PERFORMANCE', $sheet[6]['L'] ?? null, $sector);
                $this->assertNotEmpty($sheet[12]['A'] ?? null, $sector);
                if ($sector !== 'gass') {
                    $this->assertStringNotContainsString('GASS', (string) ($sheet[12]['A'] ?? ''), $sector);
                    $this->assertArrayNotHasKey('A', $sheet[13] ?? [], $sector);
                }
            } finally {
                @unlink($path);
            }
        }
    }

    public function test_penro_excel_export_is_scoped_to_its_assigned_service_area(): void
    {
        $now = now();
        DB::table('office_types')->insert([
            ['name' => 'RO', 'desc' => 'Regional Office', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PENRO', 'desc' => 'PENRO', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CENRO', 'desc' => 'CENRO', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $penroTypeId = (int) DB::table('office_types')->where('name', 'PENRO')->value('id');
        $cenroTypeId = (int) DB::table('office_types')->where('name', 'CENRO')->value('id');
        $abraId = DB::table('offices')->insertGetId([
            'name' => 'ABRA', 'office_types_id' => $penroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $banguedId = DB::table('offices')->insertGetId([
            'name' => 'BANGUED', 'office_types_id' => $cenroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $lagangilangId = DB::table('offices')->insertGetId([
            'name' => 'LAGANGILANG', 'office_types_id' => $cenroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $connerId = DB::table('offices')->insertGetId([
            'name' => 'CONNER', 'office_types_id' => $cenroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $penro = User::query()->create([
            'name' => 'PENRO Abra Export',
            'email' => 'penro-abra-export@example.test',
            'password' => 'Password1!',
            'role' => 'penro',
            'office_id' => $abraId,
        ]);

        $this->actingAs($penro);

        $method = new ReflectionMethod(WfpExcelExportController::class, 'scopedOfficeIds');
        $officeIds = $method->invoke(new WfpExcelExportController);

        $this->assertEqualsCanonicalizing([$abraId, $banguedId, $lagangilangId], $officeIds);
        $this->assertNotContains($connerId, $officeIds);
    }

    public function test_penro_export_displays_each_service_area_office_when_no_values_exist(): void
    {
        $now = now();
        DB::table('office_types')->insert([
            ['name' => 'RO', 'desc' => 'Regional Office', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PENRO', 'desc' => 'PENRO', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CENRO', 'desc' => 'CENRO', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $penroTypeId = (int) DB::table('office_types')->where('name', 'PENRO')->value('id');
        $cenroTypeId = (int) DB::table('office_types')->where('name', 'CENRO')->value('id');
        $abraId = DB::table('offices')->insertGetId([
            'name' => 'ABRA', 'office_types_id' => $penroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $banguedId = DB::table('offices')->insertGetId([
            'name' => 'BANGUED', 'office_types_id' => $cenroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $lagangilangId = DB::table('offices')->insertGetId([
            'name' => 'LAGANGILANG', 'office_types_id' => $cenroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $typeId = DB::table('types')->insertGetId([
            'code' => 'ENF', 'desc' => 'Enforcement', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $recordTypeId = DB::table('record_types')->insertGetId([
            'name' => 'PROGRAM', 'desc' => 'Program', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $indicatorId = DB::table('indicators')->insertGetId([
            'name' => 'Sample performance indicator', 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('ppa')->insert([
            'name' => 'Sample P/A/P',
            'types_id' => $typeId,
            'record_type_id' => $recordTypeId,
            'indicator_id' => $indicatorId,
            'office_id' => json_encode([$abraId, $banguedId, $lagangilangId]),
            'year' => 2026,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $method = new ReflectionMethod(WfpExcelExportController::class, 'exportRows');
        $rows = $method->invoke(
            new WfpExcelExportController,
            'enf',
            'ENF',
            2026,
            [$abraId, $banguedId, $lagangilangId]
        );

        $this->assertCount(5, $rows);
        $this->assertSame(['CAR', 'ABRA', 'PENRO', 'BANGUED', 'LAGANGILANG'], array_column($rows, 'office'));
        $this->assertSame(['Sample P/A/P'], array_values(array_unique(array_column($rows, 'pap'))));
        $this->assertSame(
            ['Sample performance indicator'],
            array_values(array_unique(array_column($rows, 'indicator')))
        );
    }

    public function test_gass_export_uses_current_ui_office_assignments_and_ignores_stale_financial_rows(): void
    {
        $now = now();
        DB::table('office_types')->insert([
            ['name' => 'RO', 'desc' => 'Regional Office', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PENRO', 'desc' => 'PENRO', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $roTypeId = (int) DB::table('office_types')->where('name', 'RO')->value('id');
        $penroTypeId = (int) DB::table('office_types')->where('name', 'PENRO')->value('id');
        $roId = DB::table('offices')->insertGetId([
            'name' => 'RO', 'office_types_id' => $roTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $abraId = DB::table('offices')->insertGetId([
            'name' => 'ABRA', 'office_types_id' => $penroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $apayaoId = DB::table('offices')->insertGetId([
            'name' => 'APAYAO', 'office_types_id' => $penroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $typeId = DB::table('types')->insertGetId([
            'code' => 'GASS', 'desc' => 'GASS', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $recordTypeId = DB::table('record_types')->insertGetId([
            'name' => 'PROGRAM', 'desc' => 'Program', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $indicatorId = DB::table('indicators')->insertGetId([
            'name' => 'Assigned indicator', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $ppaId = DB::table('ppa')->insertGetId([
            'name' => 'Assigned P/A/P',
            'types_id' => $typeId,
            'record_type_id' => $recordTypeId,
            'indicator_id' => $indicatorId,
            'office_id' => json_encode([$roId, $abraId]),
            'year' => 2026,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([[$roId, 10], [$abraId, 20], [$apayaoId, 999]] as [$officeId, $january]) {
            DB::table('financial_target')->insert([
                'sector' => 'gass',
                'office_id' => $officeId,
                'program_id' => $ppaId,
                'row_id' => $ppaId,
                'indicator_id' => $indicatorId,
                'year' => 2026,
                'jan' => $january,
                'car_totals' => json_encode(['jan' => 5000]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        DB::table('physical_accomplishments')->insert([
            'sector' => 'gass',
            'office_id' => $abraId,
            'program_id' => $ppaId,
            'row_id' => $ppaId,
            'indicator_id' => $indicatorId,
            'year' => 2026,
            'jan' => 1,
            'remarks' => 'Same remark shown in the UI',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $method = new ReflectionMethod(WfpExcelExportController::class, 'exportRows');
        $rows = collect($method->invoke(
            new WfpExcelExportController,
            'gass',
            'GASS',
            2026,
            null
        ));
        $financialRows = $rows->where('is_financial_summary', true)->keyBy('office');
        $detailOffices = $rows
            ->where('is_financial_summary', false)
            ->pluck('office')
            ->values()
            ->all();

        $this->assertSame(30.0, $financialRows['CAR']['financial_target']['jan']);
        $this->assertSame(10.0, $financialRows['RO']['financial_target']['jan']);
        $this->assertSame(20.0, $financialRows['ABRA']['financial_target']['jan']);
        $this->assertSame([], $financialRows['APAYAO']['financial_target']);
        $this->assertSame(['CAR', 'RO', 'ABRA', 'PENRO'], $detailOffices);
        $this->assertSame('Same remark shown in the UI', $rows->firstWhere('office', 'PENRO')['remarks']);
    }
}
