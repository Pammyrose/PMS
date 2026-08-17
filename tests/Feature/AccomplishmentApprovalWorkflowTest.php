<?php

namespace Tests\Feature;

use App\Models\AccomplishmentSubmission;
use App\Models\Office;
use App\Models\PhysicalAccomplishment;
use App\Models\PhysicalTarget;
use App\Models\User;
use App\Services\AccomplishmentSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccomplishmentApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_accomplishment_is_pending_until_its_penro_approves_it(): void
    {
        [$user, $penro, $programId, $indicatorId] = $this->makeApprovalFixtures();

        PhysicalTarget::query()->create([
            'sector' => 'gass',
            'user_id' => $penro->id,
            'office_id' => $user->office_id,
            'program_id' => $programId,
            'row_id' => $programId,
            'indicator_id' => $indicatorId,
            'year' => 2026,
            'jan' => 20,
            'q1' => 20,
            'annual_total' => 20,
        ]);

        $queued = app(AccomplishmentSubmissionService::class)->queue($user, 'physical', 'gass', [[
            'program_id' => $programId,
            'row_id' => $programId,
            'indicator_id' => $indicatorId,
            'office_id' => $user->office_id,
            'year' => 2026,
            'jan' => 12.5,
            'remarks' => 'Submitted accomplishment',
        ]]);

        $this->assertSame(1, $queued);
        $this->assertDatabaseMissing('physical_accomplishments', [
            'sector' => 'gass',
            'office_id' => $user->office_id,
            'indicator_id' => $indicatorId,
        ]);

        $submission = AccomplishmentSubmission::query()->sole();
        $this->assertSame('pending', $submission->status);
        $this->assertSame((int) $penro->office_id, (int) $submission->penro_office_id);
        $this->assertSame(['jan'], $submission->payload['_changed_periods']);

        $this->actingAs($penro)
            ->getJson(route('notifications.count'))
            ->assertOk()
            ->assertJson([
                'count' => 1,
                'label' => 'pending accomplishment notifications',
            ])
            ->assertJsonStructure(['version']);

        $this->actingAs($penro)
            ->get(route('penro.submissions.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('1 pending accomplishment notifications')
            ->assertSee('notificationResults')
            ->assertSee('notification-content.js')
            ->assertSee('Test indicator')
            ->assertSee('CENRO User')
            ->assertSeeInOrder(['Target: 20', 'User input: 13']);

        app(AccomplishmentSubmissionService::class)->approve($submission, $penro);

        $saved = PhysicalAccomplishment::query()->sole();
        $this->assertSame((int) $user->id, (int) $saved->user_id);
        $this->assertSame(12.5, $saved->jan);
        $this->assertSame('Submitted accomplishment', $saved->remarks);
        $this->assertSame('approved', $submission->fresh()->status);

        $this->actingAs($penro)
            ->getJson(route('notifications.count'))
            ->assertOk()
            ->assertJson(['count' => 0]);

        $this->actingAs($user)
            ->getJson(route('notifications.count'))
            ->assertOk()
            ->assertJson([
                'count' => 1,
                'label' => 'unread submission notifications',
            ]);
    }

    public function test_user_cannot_call_the_physical_target_save_route(): void
    {
        [$user] = $this->makeApprovalFixtures();

        $this->actingAs($user)
            ->postJson(route('users.gass_physical.targets.store'), ['entries' => []])
            ->assertForbidden();
    }

    public function test_penro_can_decline_without_saving_the_accomplishment(): void
    {
        [$user, $penro, $programId, $indicatorId] = $this->makeApprovalFixtures();

        PhysicalTarget::query()->create([
            'sector' => 'gass',
            'user_id' => $penro->id,
            'office_id' => $user->office_id,
            'program_id' => $programId,
            'row_id' => $programId,
            'indicator_id' => $indicatorId,
            'year' => 2026,
            'jan' => 10,
        ]);

        app(AccomplishmentSubmissionService::class)->queue($user, 'physical', 'gass', [[
            'program_id' => $programId,
            'row_id' => $programId,
            'indicator_id' => $indicatorId,
            'office_id' => $user->office_id,
            'year' => 2026,
            'jan' => 8,
        ]]);

        $submission = AccomplishmentSubmission::query()->sole();

        $this->actingAs($penro)
            ->patch(route('penro.submissions.decline', $submission), [
                'submission_id' => $submission->id,
                'review_notes' => 'Please attach supporting details.',
            ])
            ->assertRedirect(
                route('penro.submissions.index', ['status' => 'declined']).'#submission-'.$submission->id
            );

        $this->assertDatabaseHas('accomplishment_submissions', [
            'id' => $submission->id,
            'status' => 'declined',
            'review_notes' => 'Please attach supporting details.',
            'user_read_at' => null,
        ]);
        $this->assertDatabaseCount('physical_accomplishments', 0);

        $this->actingAs($penro)
            ->get(route('penro.submissions.index', ['status' => 'declined']))
            ->assertOk()
            ->assertSee('Please attach supporting details.');

        $this->actingAs($user)
            ->get(route('notifications.index', ['status' => 'declined']))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('New')
            ->assertSee('Reason for decline')
            ->assertSee('Please attach supporting details.')
            ->assertSee('PENRO Reviewer')
            ->assertSeeInOrder(['Target: 10', 'User input: 8'])
            ->assertDontSee('View entry')
            ->assertDontSee('highlight_period');

        $this->assertNotNull($submission->fresh()->user_read_at);
    }

    private function makeApprovalFixtures(): array
    {
        $now = now();
        DB::table('office_types')->insert([
            'name' => 'RO', 'desc' => 'Regional Office', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $penroTypeId = DB::table('office_types')->insertGetId([
            'name' => 'PENRO', 'desc' => 'PENRO', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $cenroTypeId = DB::table('office_types')->insertGetId([
            'name' => 'CENRO', 'desc' => 'CENRO', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $penroOfficeId = DB::table('offices')->insertGetId([
            'name' => 'ABRA', 'office_types_id' => $penroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $cenroOfficeId = DB::table('offices')->insertGetId([
            'name' => 'BANGUED', 'office_types_id' => $cenroTypeId, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $typeId = DB::table('types')->insertGetId([
            'code' => 'GASS', 'desc' => 'GASS', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $recordTypeId = DB::table('record_types')->insertGetId([
            'name' => 'PROGRAM', 'desc' => 'Program', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $detailId = DB::table('ppa_details')->insertGetId([
            'parent_id' => null, 'column_order' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $indicatorId = DB::table('indicators')->insertGetId([
            'name' => 'Test indicator', 'indicator_type_id' => null, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $programId = DB::table('ppa')->insertGetId([
            'name' => 'Test program',
            'types_id' => $typeId,
            'record_type_id' => $recordTypeId,
            'ppa_details_id' => $detailId,
            'indicator_id' => $indicatorId,
            'office_id' => json_encode([$cenroOfficeId]),
            'year' => 2026,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $user = User::query()->create([
            'name' => 'CENRO User',
            'email' => 'cenro@example.test',
            'password' => 'Password1!',
            'role' => 'cenro',
            'office_id' => $cenroOfficeId,
        ]);
        $penro = User::query()->create([
            'name' => 'PENRO Reviewer',
            'email' => 'penro@example.test',
            'password' => 'Password1!',
            'role' => 'penro',
            'office_id' => $penroOfficeId,
        ]);

        $this->assertSame($penroOfficeId, Office::penroOfficeIdFor($cenroOfficeId));

        return [$user, $penro, $programId, $indicatorId];
    }
}
