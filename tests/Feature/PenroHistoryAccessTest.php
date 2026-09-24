<?php

namespace Tests\Feature;

use App\Models\EditHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PenroHistoryAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_penro_sees_history_for_its_service_area_only(): void
    {
        [$penro, $abraUser, $apayaUser] = $this->makeUsers();

        $this->recordFor($penro, 'PENRO Abra activity');
        $this->recordFor($abraUser, 'CENRO Bangued activity');
        $this->recordFor($apayaUser, 'CENRO Conner activity');

        $this->actingAs($penro)
            ->get(route('history'))
            ->assertOk()
            ->assertSee('History')
            ->assertSee('PENRO Abra activity')
            ->assertSee('CENRO Bangued activity')
            ->assertDontSee('CENRO Conner activity');
    }

    public function test_cenro_cannot_open_history(): void
    {
        [, $cenro] = $this->makeUsers();

        $this->actingAs($cenro)
            ->get(route('history'))
            ->assertForbidden();
    }

    public function test_regional_office_can_open_history_and_see_all_offices(): void
    {
        [$penro, $abraUser, $apayaUser, $regional] = $this->makeUsers();

        $this->recordFor($penro, 'PENRO Abra activity');
        $this->recordFor($abraUser, 'CENRO Bangued activity');
        $this->recordFor($apayaUser, 'CENRO Conner activity');

        $this->actingAs($regional)
            ->get(route('history'))
            ->assertOk()
            ->assertSee('History')
            ->assertSee('PENRO Abra activity')
            ->assertSee('CENRO Bangued activity')
            ->assertSee('CENRO Conner activity');
    }

    private function makeUsers(): array
    {
        $now = now();
        DB::table('office_types')->insert([
            ['name' => 'RO', 'desc' => 'Regional Office', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'PENRO', 'desc' => 'PENRO', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CENRO', 'desc' => 'CENRO', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $carId = DB::table('offices')->insertGetId([
            'name' => 'CAR', 'office_types_id' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $abraId = DB::table('offices')->insertGetId([
            'name' => 'ABRA', 'office_types_id' => 2, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $banguedId = DB::table('offices')->insertGetId([
            'name' => 'BANGUED', 'office_types_id' => 3, 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('offices')->insertGetId([
            'name' => 'APAYAO', 'office_types_id' => 2, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $connerId = DB::table('offices')->insertGetId([
            'name' => 'CONNER', 'office_types_id' => 3, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $penro = User::query()->create([
            'name' => 'PENRO Abra activity',
            'email' => 'penro-abra@example.test',
            'password' => 'Password1!',
            'role' => 'penro',
            'office_id' => $abraId,
        ]);
        $abraUser = User::query()->create([
            'name' => 'CENRO Bangued activity',
            'email' => 'bangued@example.test',
            'password' => 'Password1!',
            'role' => 'cenro',
            'office_id' => $banguedId,
        ]);
        $apayaUser = User::query()->create([
            'name' => 'CENRO Conner activity',
            'email' => 'conner@example.test',
            'password' => 'Password1!',
            'role' => 'cenro',
            'office_id' => $connerId,
        ]);
        $regional = User::query()->create([
            'name' => 'Regional Office',
            'email' => 'regional-history@example.test',
            'password' => 'Password1!',
            'role' => 'ro-office',
            'office_id' => $carId,
        ]);

        return [$penro, $abraUser, $apayaUser, $regional];
    }

    private function recordFor(User $user, string $name): void
    {
        EditHistory::query()->create([
            'user_id' => $user->id,
            'user_name' => $name,
            'user_role' => $user->role,
            'module' => 'GASS',
            'edited_part' => 'accomplishments',
            'action' => 'store',
            'changed_fields' => ['entries.jan'],
            'request_snapshot' => ['changed_data' => ['Jan: 1']],
        ]);
    }
}
