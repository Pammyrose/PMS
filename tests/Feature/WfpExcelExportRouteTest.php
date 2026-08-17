<?php

namespace Tests\Feature;

use App\Http\Controllers\PhysicalExcelUploadController;
use App\Http\Controllers\WfpExcelExportController;
use App\Models\User;
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

    public function test_user_sector_toolbars_do_not_render_excel_controls(): void
    {
        foreach (['gass', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing'] as $sector) {
            $this->view("users.{$sector}.partials.{$sector}_physical_toolbar")
                ->assertDontSee('Upload Excel')
                ->assertDontSee('Generate Excel');
        }
    }

    public function test_user_and_cenro_roles_cannot_generate_excel_exports(): void
    {
        foreach (['user', 'cenro'] as $role) {
            $user = User::query()->create([
                'name' => strtoupper($role) . ' Export Test',
                'email' => "{$role}-export@example.test",
                'password' => 'Password1!',
                'role' => $role,
            ]);

            $this->actingAs($user)
                ->get('/wfp/export/gass?year=2026')
                ->assertForbidden();

            auth()->logout();
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

        $this->assertCount(3, $rows);
        $this->assertEqualsCanonicalizing(['ABRA', 'BANGUED', 'LAGANGILANG'], array_column($rows, 'office'));
        $this->assertSame(['Sample P/A/P'], array_values(array_unique(array_column($rows, 'pap'))));
        $this->assertSame(
            ['Sample performance indicator'],
            array_values(array_unique(array_column($rows, 'indicator')))
        );
    }
}
