<?php

namespace Tests\Feature;

use App\Http\Controllers\GassController;
use App\Models\User;
use App\Support\GassUiReport;
use App\Support\SimpleXlsxReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GassUiReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sto_export_uses_its_ui_assignments_and_first_pap_financial_summary(): void
    {
        $admin = User::query()->create(['name' => 'STO tester', 'email' => 'sto-ui@example.test', 'password' => 'Password1!', 'role' => 'admin']);
        $this->actingAs($admin);
        $type = DB::table('types')->insertGetId(['code' => 'STO', 'desc' => 'STO']);
        $recordTypes = [];
        foreach (['PROGRAM', 'PROJECT', 'MAIN ACTIVITY', 'SUB-ACTIVITY', 'SUB-SUB-ACTIVITY', 'SUB-SUB-SUB-ACTIVITY', 'LEVEL-7', 'LEVEL-8', 'LEVEL-9'] as $name) {
            $recordTypes[] = DB::table('record_types')->insertGetId(['name' => $name, 'desc' => $name]);
        }
        DB::table('office_types')->insert([['name' => 'RO', 'desc' => 'RO'], ['name' => 'PENRO', 'desc' => 'PENRO']]);
        $ro = DB::table('offices')->insertGetId(['name' => 'RO', 'office_types_id' => 1]);
        $abra = DB::table('offices')->insertGetId(['name' => 'ABRA', 'office_types_id' => 2]);
        $apayao = DB::table('offices')->insertGetId(['name' => 'APAYAO', 'office_types_id' => 2]);
        $indicator = DB::table('indicators')->insertGetId(['name' => 'STO indicator']);
        $parent = null;
        foreach (['STO MAJOR PAP', 'N/A', 'N/A', 'Activity group', 'STO activity'] as $index => $name) {
            $detail = DB::table('ppa_details')->insertGetId(['parent_id' => $parent, 'column_order' => $index + 1, 'source_order' => $index + 1]);
            $row = DB::table('ppa')->insertGetId([
                'name' => $name, 'types_id' => $type, 'record_type_id' => $recordTypes[$index],
                'ppa_details_id' => $detail, 'indicator_id' => $index === 4 ? $indicator : null,
                'office_id' => json_encode($index === 4 ? [$ro, $abra] : []), 'year' => 2026,
            ]);
            $parent = $detail;
        }
        foreach ([[$ro, 10], [$abra, 20], [$apayao, 999]] as [$office, $amount]) {
            DB::table('financial_target')->insert([
                'sector' => 'sto', 'program_id' => $row, 'row_id' => $row,
                'indicator_id' => $indicator, 'office_id' => $office,
                'year' => 2026, 'jan' => $amount,
            ]);
        }
        $rows = app(GassUiReport::class)->rows(Request::create('/', 'GET'), 2026, 'sto');
        $summary = collect($rows)->where('is_financial_summary', true)->keyBy('office');
        $this->assertCount(8, $summary);
        $this->assertSame(30.0, $summary['CAR']['financial_target']['jan']);
        $this->assertSame(10.0, $summary['RO']['financial_target']['jan']);
        $this->assertSame(20.0, $summary['ABRA']['financial_target']['jan']);
        $this->assertSame(0.0, $summary['APAYAO']['financial_target']['jan']);
        $this->assertSame(['CAR', 'RO', 'ABRA', 'PENRO'], collect($rows)->where('indicator', 'STO indicator')->pluck('office')->all());
    }

    public function test_download_follows_rendered_ui_groups_assignments_and_summary_values(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 15));
        $admin = User::query()->create(['name' => 'Export tester', 'email' => 'export-ui@example.test', 'password' => 'Password1!', 'role' => 'admin']);
        $this->actingAs($admin);
        $type = DB::table('types')->insertGetId(['code' => 'GASS', 'desc' => 'GASS']);
        $recordTypes = [];
        foreach (['PROGRAM', 'PROJECT', 'MAIN ACTIVITY', 'SUB-ACTIVITY', 'SUB-SUB-ACTIVITY', 'SUB-SUB-SUB-ACTIVITY', 'LEVEL-7', 'LEVEL-8', 'LEVEL-9'] as $name) {
            $recordTypes[] = DB::table('record_types')->insertGetId(['name' => $name, 'desc' => $name]);
        }
        foreach (['RO', 'PENRO', 'CENRO'] as $name) {
            DB::table('office_types')->insert(['name' => $name, 'desc' => $name]);
        }
        $ro = DB::table('offices')->insertGetId(['name' => 'RO', 'office_types_id' => 1]);
        $abra = DB::table('offices')->insertGetId(['name' => 'ABRA', 'office_types_id' => 2]);
        $bangued = DB::table('offices')->insertGetId(['name' => 'BANGUED', 'office_types_id' => 3]);
        $apayao = DB::table('offices')->insertGetId(['name' => 'APAYAO', 'office_types_id' => 2]);
        $indicatorType = DB::table('indicator_types')->insertGetId(['name' => 'Non-cumulative']);
        $indicator = DB::table('indicators')->insertGetId(['name' => 'Maintained offices', 'indicator_type_id' => $indicatorType]);
        $secondIndicator = DB::table('indicators')->insertGetId(['name' => 'Additional indicator from saved physical rows']);
        $add = function (string $name, int $level, ?int $parent = null, ?int $indicatorId = null, array $offices = [], ?int $year = null) use ($type, $recordTypes): array {
            $detail = DB::table('ppa_details')->insertGetId(['parent_id' => $parent, 'column_order' => $level, 'source_order' => DB::table('ppa_details')->count() + 1]);
            $ppa = DB::table('ppa')->insertGetId(['name' => $name, 'types_id' => $type, 'record_type_id' => $recordTypes[$level - 1], 'ppa_details_id' => $detail, 'indicator_id' => $indicatorId, 'office_id' => json_encode($offices), 'year' => $year]);

            return [$ppa, $detail];
        };
        [$root, $rootDetail] = $add('Z. FIRST PAP IN UI ORDER', 1, year: 2026);
        [, $project] = $add('N/A', 2, $rootDetail);
        [, $main] = $add('N/A', 3, $project);
        [, $activity] = $add('Activity group', 4, $main);
        [$leaf] = $add('First child', 5, $activity, $indicator, [$ro, $abra, $bangued]);
        [$otherLeaf] = $add('Second child', 5, $activity, $secondIndicator, [$ro]);
        $add('A. SECOND PAP WITHOUT INDICATORS', 1, year: 2026);
        $add('Old year must not appear', 1, indicatorId: $indicator, offices: [$apayao], year: 2025);
        $base = ['sector' => 'gass', 'program_id' => $root, 'row_id' => $leaf, 'indicator_id' => $indicator, 'year' => 2026];
        foreach ([[$ro, 3, 10], [$abra, 5, 20], [$bangued, 4, 30]] as [$office, $physical, $financial]) {
            DB::table('physical_targets')->insert([...$base, 'office_id' => $office, 'jan' => $physical, 'jul' => $physical]);
            DB::table('physical_accomplishments')->insert([...$base, 'office_id' => $office, 'jan' => 1, 'dec' => 100]);
            // Financial program_id differs; the UI addresses these by row_id.
            DB::table('financial_target')->insert([...$base, 'program_id' => $leaf, 'office_id' => $office, 'jan' => $financial]);
        }
        DB::table('financial_target')->insert([...$base, 'office_id' => $apayao, 'jan' => 9999]);
        DB::table('physical_targets')->insert([...$base, 'row_id' => $otherLeaf, 'indicator_id' => $secondIndicator, 'office_id' => $ro, 'jan' => 7]);
        // An additional indicator is present in the actual UI even though the
        // PPA's single indicator_id column cannot describe all of its rows.
        DB::table('physical_targets')->insert([...$base, 'indicator_id' => $secondIndicator, 'office_id' => $ro, 'jan' => 2]);

        $request = Request::create('/', 'GET', ['year' => 2026]);
        $page = app(GassController::class)->index($request);
        $html = view('admin.gass.partials.gass_physical_table_rows', $page->getData())->render();
        $this->assertSame(2, substr_count($html, 'class="data-row default-office-unit-row"'));
        $this->assertStringNotContainsString('Old year must not appear', $html);
        $report = app(GassUiReport::class)->rows($request, 2026);
        $summaries = array_values(array_filter($report, fn ($row) => $row['is_financial_summary'] ?? false));
        $this->assertCount(16, $summaries);
        $this->assertSame(60.0, $summaries[0]['financial_target']['jan']);
        $this->assertSame(50.0, $summaries[2]['financial_target']['jan']);
        $this->assertEquals(0, $summaries[3]['financial_target']['jan']);
        $details = array_values(array_filter($report, fn ($row) => ($row['indicator'] ?? '') === 'Maintained offices'));
        $this->assertSame(['CAR', 'RO', 'ABRA', 'PENRO', 'BANGUED'], array_column($details, 'office'));
        $this->assertSame(5.0, $details[0]['physical_target']['jan']);
        $this->assertSame(5.0, $details[2]['physical_target']['jan']);
        $this->assertSame(substr_count($html, 'class="office-line car-office-line"'), count(array_filter($report, fn ($row) => ($row['office'] ?? '') === 'CAR')));

        $response = $this->get('/wfp/export/gass?year=2026')->assertOk();
        $path = $response->baseResponse->getFile()->getPathname();
        try {
            $sheet = iterator_to_array((new SimpleXlsxReader)->rows($path, 'GASS', false));
            $car = collect($sheet)->first(fn ($row) => ($row['B'] ?? '') === 'Maintained offices' && ($row['C'] ?? '') === 'CAR');
            $this->assertSame('0', $car['D']); // Two zero months make each sparse quarter zero.
            $this->assertSame('0', $car['H']); // The quarter uses its repeated zero month value.
            $this->assertSame('0', $car['J']); // A zero annual target produces zero percent accomplishment.
            foreach (range('K', 'S') as $column) {
                $this->assertArrayNotHasKey($column, $car);
            }
            $this->assertSame('60', $sheet[15]['L']);
            $this->assertSame('50', $sheet[17]['L']);
        } finally {
            @unlink($path);
        }

        $penro = User::query()->create(['name' => 'Abra tester', 'email' => 'abra-export@example.test', 'password' => 'Password1!', 'role' => 'penro', 'office_id' => $abra]);
        $this->actingAs($penro);
        $scoped = app(GassUiReport::class)->rows($request, 2026);
        $scopedSummaries = array_values(array_filter($scoped, fn ($row) => $row['is_financial_summary'] ?? false));
        $scopedDetails = array_values(array_filter($scoped, fn ($row) => ($row['indicator'] ?? '') === 'Maintained offices'));
        // PENRO's page omits the separate province subtotal line.
        $this->assertSame(['CAR', 'PENRO', 'BANGUED'], array_column($scopedDetails, 'office'));
        $this->assertSame(50.0, $scopedSummaries[0]['financial_target']['jan']);
        $this->assertEquals(0, $scopedSummaries[1]['financial_target']['jan']);
    }
}
