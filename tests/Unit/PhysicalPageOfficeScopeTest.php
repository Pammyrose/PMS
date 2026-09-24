<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PhysicalPageOfficeScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('office_types_id');
        });

        DB::table('offices')->insert([
            ['id' => 4, 'name' => 'BENGUET', 'office_types_id' => 2],
            ['id' => 8, 'name' => 'BUGUIAS', 'office_types_id' => 3],
            ['id' => 9, 'name' => 'BAGUIO', 'office_types_id' => 3],
            ['id' => 10, 'name' => 'PARACELIS', 'office_types_id' => 3],
        ]);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('offices');

        parent::tearDown();
    }

    public function test_penro_physical_page_keeps_its_penro_and_cenro_rows(): void
    {
        $controller = new class extends Controller
        {
            public function scope(int $officeId): array
            {
                return $this->officeIdsForPhysicalPageScope($officeId);
            }

            public function indicators(Collection $indicators, int $officeId): Collection
            {
                return $this->filterIndicatorsForOffice($indicators, $officeId);
            }

            public function sectionData(array $sectionData, int $officeId): array
            {
                return $this->filterSectionDataForOffice($sectionData, $officeId);
            }
        };

        $user = new User;
        $user->forceFill(['id' => 1, 'role' => 'penro', 'office_id' => 4]);
        auth()->setUser($user);

        $indicators = collect([
            100 => collect([
                (object) ['id' => 1, 'office_id' => [4, 8, 9, 10]],
                (object) ['id' => 2, 'office_id' => [10]],
            ]),
        ]);
        $sectionData = [
            100 => [
                1 => [
                    '4' => ['value' => 1],
                    '8' => ['value' => 2],
                    '9' => ['value' => 3],
                    '10' => ['value' => 4],
                ],
            ],
        ];

        $filteredIndicators = $controller->indicators($indicators, 4);
        $filteredSectionData = $controller->sectionData($sectionData, 4);

        $this->assertSame([4, 8, 9], $controller->scope(4));
        $this->assertCount(1, $filteredIndicators[100]);
        $this->assertSame([4, 8, 9], $filteredIndicators[100]->first()->office_id);
        $this->assertSame([4, 8, 9], array_keys($filteredSectionData[100][1]));

        $user->forceFill(['role' => 'cenro', 'office_id' => 8]);
        auth()->setUser($user);

        $cenroIndicators = $controller->indicators($indicators, 8);
        $cenroSectionData = $controller->sectionData($sectionData, 8);

        $this->assertSame([8], $controller->scope(8));
        $this->assertSame([8], $cenroIndicators[100]->first()->office_id);
        $this->assertSame([8], array_keys($cenroSectionData[100][1]));
    }
}
