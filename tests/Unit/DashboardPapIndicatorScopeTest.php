<?php

namespace Tests\Unit;

use App\Http\Controllers\DashboardController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class DashboardPapIndicatorScopeTest extends TestCase
{
    private array $tables = [
        'types',
        'record_types',
        'indicators',
        'ppa_details',
        'ppa',
        'offices',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('code');
        });
        Schema::create('record_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->text('name');
        });
        Schema::create('ppa_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
        });
        Schema::create('ppa', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('types_id');
            $table->unsignedBigInteger('record_type_id');
            $table->unsignedBigInteger('ppa_details_id')->nullable();
            $table->unsignedBigInteger('indicator_id')->nullable();
            $table->json('office_id')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
        });
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('office_types_id');
        });

        DB::table('types')->insert(['id' => 1, 'code' => 'GASS']);
        DB::table('record_types')->insert([
            ['id' => 1, 'name' => 'PROGRAM'],
            ['id' => 2, 'name' => 'PROJECT'],
        ]);
        DB::table('indicators')->insert([
            ['id' => 10, 'name' => 'PENRO indicator'],
            ['id' => 11, 'name' => 'CENRO indicator'],
        ]);
        DB::table('offices')->insert([
            ['id' => 4, 'name' => 'BENGUET', 'office_types_id' => 2],
            ['id' => 8, 'name' => 'BUGUIAS', 'office_types_id' => 3],
            ['id' => 9, 'name' => 'BAGUIO', 'office_types_id' => 3],
            ['id' => 10, 'name' => 'PARACELIS', 'office_types_id' => 3],
        ]);
        DB::table('ppa_details')->insert([
            ['id' => 100, 'parent_id' => null],
            ['id' => 101, 'parent_id' => 100],
            ['id' => 102, 'parent_id' => 100],
        ]);
        DB::table('ppa')->insert([
            [
                'id' => 1000,
                'name' => 'Parent P/A/P',
                'types_id' => 1,
                'record_type_id' => 1,
                'ppa_details_id' => 100,
                'indicator_id' => null,
                'office_id' => null,
                'year' => 2026,
            ],
            [
                'id' => 1001,
                'name' => 'PENRO activity',
                'types_id' => 1,
                'record_type_id' => 2,
                'ppa_details_id' => 101,
                'indicator_id' => 10,
                'office_id' => json_encode([4]),
                'year' => null,
            ],
            [
                'id' => 1002,
                'name' => 'CENRO activity',
                'types_id' => 1,
                'record_type_id' => 2,
                'ppa_details_id' => 102,
                'indicator_id' => 11,
                'office_id' => json_encode([8]),
                'year' => null,
            ],
        ]);
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            Schema::dropIfExists($table);
        }

        parent::tearDown();
    }

    public function test_penro_counts_only_the_selected_office_below_an_unassigned_parent_pap(): void
    {
        $controller = new DashboardController;
        $fieldConfigs = [[
            'key' => 'gass',
            'label' => 'GASS',
            'type_code' => 'GASS',
            'targets' => 'missing_physical_targets',
            'accomp' => 'missing_physical_accomplishments',
        ]];

        $totalsMethod = new ReflectionMethod(DashboardController::class, 'papIndicatorTotalsForYear');
        $papListMethod = new ReflectionMethod(DashboardController::class, 'papListForYear');
        $indicatorListMethod = new ReflectionMethod(DashboardController::class, 'indicatorListForYear');
        $officeFilterMethod = new ReflectionMethod(DashboardController::class, 'dashboardOfficeFilter');
        $officeScopeNamesMethod = new ReflectionMethod(DashboardController::class, 'dashboardOfficeScopeNames');

        $user = new User;
        $user->forceFill(['id' => 1, 'role' => 'penro', 'office_id' => 4]);
        auth()->setUser($user);
        $officeScope = $officeFilterMethod->invoke(
            $controller,
            \Illuminate\Http\Request::create('/dashboard', 'GET', ['office_id' => '4'])
        )['scope'];

        $totals = $totalsMethod->invoke($controller, $fieldConfigs, 2026, $officeScope);
        $papList = $papListMethod->invoke($controller, $fieldConfigs, 2026, $officeScope);
        $indicatorList = $indicatorListMethod->invoke($controller, $fieldConfigs, 2026, $officeScope);

        $this->assertSame([4], $officeScope);
        $this->assertSame(['BENGUET'], $officeScopeNamesMethod->invoke($controller, $officeScope));
        $this->assertSame(['pap_total' => 1, 'indicator_total' => 1], $totals);
        $this->assertCount(1, $papList);
        $this->assertSame('Parent P/A/P', $papList->first()['name']);
        $this->assertSame(1, $papList->first()['indicator_count']);
        $this->assertSame(['BENGUET'], $papList->first()['offices']);
        $this->assertCount(1, $indicatorList);
        $this->assertSame(['PENRO indicator'], $indicatorList->pluck('name')->all());
        $this->assertSame(['BENGUET'], $indicatorList->flatMap(fn (array $indicator) => $indicator['offices'])->all());
    }

    public function test_dashboard_office_filter_respects_role_access(): void
    {
        $method = new ReflectionMethod(DashboardController::class, 'dashboardOfficeFilter');

        $penro = new User;
        $penro->forceFill(['id' => 1, 'role' => 'penro', 'office_id' => 4]);
        auth()->setUser($penro);

        $penroAll = $method->invoke(
            new DashboardController,
            \Illuminate\Http\Request::create('/dashboard', 'GET', ['office_id' => 'all'])
        );
        $penroDefault = $method->invoke(
            new DashboardController,
            \Illuminate\Http\Request::create('/dashboard', 'GET')
        );
        $penroCenro = $method->invoke(
            new DashboardController,
            \Illuminate\Http\Request::create('/dashboard', 'GET', ['office_id' => '8'])
        );
        $penroUnauthorized = $method->invoke(
            new DashboardController,
            \Illuminate\Http\Request::create('/dashboard', 'GET', ['office_id' => '10'])
        );

        $this->assertTrue($penroAll['allows_all']);
        $this->assertSame('All Service Area', $penroAll['all_label']);
        $this->assertSame('all', $penroAll['selected']);
        $this->assertSame([4, 8, 9], $penroAll['scope']);
        $this->assertSame([4, 9, 8], $penroAll['options']->pluck('id')->all());
        $this->assertSame('all', $penroDefault['selected']);
        $this->assertSame([4, 8, 9], $penroDefault['scope']);
        $this->assertSame('8', $penroCenro['selected']);
        $this->assertSame([8], $penroCenro['scope']);
        $this->assertSame('all', $penroUnauthorized['selected']);
        $this->assertSame([4, 8, 9], $penroUnauthorized['scope']);

        $cenro = new User;
        $cenro->forceFill(['id' => 2, 'role' => 'cenro', 'office_id' => 8]);
        auth()->setUser($cenro);

        $cenroFilter = $method->invoke(
            new DashboardController,
            \Illuminate\Http\Request::create('/dashboard', 'GET', ['office_id' => '4'])
        );

        $this->assertFalse($cenroFilter['allows_all']);
        $this->assertSame('8', $cenroFilter['selected']);
        $this->assertSame([8], $cenroFilter['scope']);

        $admin = new User;
        $admin->forceFill(['id' => 3, 'role' => 'admin', 'office_id' => 1]);
        auth()->setUser($admin);

        $adminFilter = $method->invoke(
            new DashboardController,
            \Illuminate\Http\Request::create('/dashboard', 'GET', ['office_id' => 'all'])
        );

        $this->assertSame('All Offices', $adminFilter['all_label']);
        $this->assertNull($adminFilter['scope']);
        $this->assertSame([4, 9, 8, 10], $adminFilter['options']->pluck('id')->all());
    }

    public function test_all_offices_counts_office_assignments_separately_from_penro(): void
    {
        DB::table('ppa')
            ->where('id', 1001)
            ->update(['office_id' => json_encode([4, 8])]);

        $controller = new DashboardController;
        $totalsMethod = new ReflectionMethod(DashboardController::class, 'papIndicatorTotalsForYear');
        $papListMethod = new ReflectionMethod(DashboardController::class, 'papListForYear');
        $indicatorListMethod = new ReflectionMethod(DashboardController::class, 'indicatorListForYear');
        $fieldConfigs = [[
            'key' => 'gass',
            'label' => 'GASS',
            'type_code' => 'GASS',
            'targets' => 'missing_physical_targets',
            'accomp' => 'missing_physical_accomplishments',
        ]];

        $allTotals = $totalsMethod->invoke($controller, $fieldConfigs, 2026, null);
        $penroTotals = $totalsMethod->invoke($controller, $fieldConfigs, 2026, [4]);
        $allPapList = $papListMethod->invoke($controller, $fieldConfigs, 2026, null);
        $allIndicatorList = $indicatorListMethod->invoke($controller, $fieldConfigs, 2026, null);

        $this->assertSame(['pap_total' => 2, 'indicator_total' => 3], $allTotals);
        $this->assertSame(['pap_total' => 1, 'indicator_total' => 1], $penroTotals);
        $this->assertCount(1, $allPapList);
        $this->assertCount(2, $allIndicatorList);
    }
}
