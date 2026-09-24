<?php

namespace Tests\Feature;

use App\Models\AccomplishmentSubmission;
use App\Models\FinancialAccomplishment;
use App\Models\Office;
use App\Models\PhysicalAccomplishment;
use App\Models\PhysicalTarget;
use App\Models\User;
use App\Services\AccomplishmentSubmissionService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccomplishmentApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cenro_accomplishments_are_saved_immediately_without_approval(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-01-15 08:00:00', 'Asia/Manila'));
        [$user, , $programId, $indicatorId] = $this->makeApprovalFixtures();

        $response = $this->actingAs($user)
            ->postJson(route('users.gass_physical.accomplishments.store'), [
                'entries' => [[
                    'program_id' => $programId,
                    'row_id' => $programId,
                    'indicator_id' => $indicatorId,
                    'office_id' => $user->office_id,
                    'year' => 2026,
                    'jan' => 12.5,
                    'q1' => 12.5,
                    'annual_total' => 12.5,
                    'remarks' => 'Saved immediately',
                ]],
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'created_count' => 1,
                'updated_count' => 0,
            ])
            ->assertJsonMissing(['pending_approval' => true]);

        $this->assertDatabaseHas('physical_accomplishments', [
            'sector' => 'gass',
            'office_id' => $user->office_id,
            'indicator_id' => $indicatorId,
            'jan' => 12.5,
            'remarks' => 'Saved immediately',
        ]);
        $this->assertDatabaseCount('accomplishment_submissions', 0);
    }

    public function test_locked_month_change_requires_a_reason_and_regional_approval(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-06-15 08:00:00', 'Asia/Manila'));
        [$user, $penro, $programId, $indicatorId, , $regional] = $this->makeApprovalFixtures();

        PhysicalAccomplishment::query()->create([
            'sector' => 'gass',
            'user_id' => $user->id,
            'office_id' => $user->office_id,
            'program_id' => $programId,
            'row_id' => $programId,
            'indicator_id' => $indicatorId,
            'year' => 2026,
            'jan' => 12.5,
            'q1' => 12.5,
            'annual_total' => 12.5,
        ]);

        $this->actingAs($user)
            ->postJson(route('users.gass_physical.accomplishments.store'), [
                'entries' => [[
                    'program_id' => $programId,
                    'row_id' => $programId,
                    'indicator_id' => $indicatorId,
                    'office_id' => $user->office_id,
                    'year' => 2026,
                    'jan' => 99,
                    'jun' => 5,
                ]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('entries');

        $saved = PhysicalAccomplishment::query()->sole();
        $this->assertSame(12.5, $saved->jan);
        $this->assertSame(0.0, $saved->jun);

        $this->actingAs($user)
            ->postJson(route('users.gass_physical.accomplishments.store'), [
                'entries' => [[
                    'program_id' => $programId,
                    'row_id' => $programId,
                    'indicator_id' => $indicatorId,
                    'office_id' => $user->office_id,
                    'year' => 2026,
                    'jan' => 99,
                    'q1' => 99,
                    'jun' => 5,
                    'q2' => 5,
                    'annual_total' => 104,
                    'change_reason' => 'Correcting an encoded January report.',
                ]],
            ])
            ->assertOk()
            ->assertJson([
                'pending_approval' => true,
                'queued_count' => 1,
            ]);

        $saved->refresh();
        $this->assertSame(12.5, $saved->jan);
        $this->assertSame(0.0, $saved->jun);

        $submission = AccomplishmentSubmission::query()->sole();
        $this->assertSame('Correcting an encoded January report.', $submission->request_reason);
        $this->assertSame(['jan'], $submission->payload['_changed_periods']);

        $this->actingAs($penro)
            ->patch(route('accomplishment-requests.approve', $submission))
            ->assertForbidden();

        $this->actingAs($regional)
            ->getJson(route('notifications.count'))
            ->assertOk()
            ->assertJson([
                'count' => 1,
                'label' => 'pending locked-period change requests',
            ]);

        $this->actingAs($regional)
            ->get(route('accomplishment-requests.index'))
            ->assertOk()
            ->assertSee('Correcting an encoded January report.');

        $this->actingAs($regional)
            ->patch(route('accomplishment-requests.approve', $submission))
            ->assertRedirect();

        $saved->refresh();
        $this->assertSame(99.0, $saved->jan);
        $this->assertSame(99.0, $saved->q1);
        $this->assertSame('approved', $submission->fresh()->status);
    }

    public function test_current_month_change_still_saves_immediately(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-06-15 08:00:00', 'Asia/Manila'));
        [$user, , $programId, $indicatorId] = $this->makeApprovalFixtures();

        $this->actingAs($user)
            ->postJson(route('users.gass_physical.accomplishments.store'), [
                'entries' => [[
                    'program_id' => $programId,
                    'row_id' => $programId,
                    'indicator_id' => $indicatorId,
                    'office_id' => $user->office_id,
                    'year' => 2026,
                    'jun' => 5,
                    'q2' => 5,
                    'annual_total' => 5,
                ]],
            ])
            ->assertOk()
            ->assertJson(['pending_approval' => false]);

        $this->assertSame(5.0, PhysicalAccomplishment::query()->sole()->jun);
        $this->assertDatabaseCount('accomplishment_submissions', 0);
    }

    public function test_cenro_financial_accomplishments_are_saved_immediately_without_approval(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-01-15 08:00:00', 'Asia/Manila'));
        [$user, , $programId, $indicatorId] = $this->makeApprovalFixtures();

        $this->actingAs($user)
            ->postJson(route('financial_inputs.store', ['sector' => 'gass']), [
                'entries' => [[
                    'program_id' => $programId,
                    'row_id' => $programId,
                    'indicator_id' => $indicatorId,
                    'office_id' => $user->office_id,
                    'year' => 2026,
                    'kind' => 'accomplishment',
                    'jan' => 250,
                    'q1' => 250,
                    'annual_total' => 250,
                ]],
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'created_count' => 1,
                'updated_count' => 0,
            ])
            ->assertJsonMissing(['pending_approval' => true]);

        $saved = FinancialAccomplishment::query()->sole();
        $this->assertSame((int) $user->id, (int) $saved->user_id);
        $this->assertSame(250.0, $saved->jan);
        $this->assertDatabaseCount('accomplishment_submissions', 0);
    }

    public function test_locked_financial_change_waits_for_admin_approval(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-06-15 08:00:00', 'Asia/Manila'));
        [$user, , $programId, $indicatorId, $admin] = $this->makeApprovalFixtures();

        FinancialAccomplishment::query()->create([
            'sector' => 'gass',
            'user_id' => $user->id,
            'office_id' => $user->office_id,
            'program_id' => $programId,
            'row_id' => $programId,
            'indicator_id' => $indicatorId,
            'year' => 2026,
            'jan' => 100,
            'q1' => 100,
            'annual_total' => 100,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('financial_inputs.store', ['sector' => 'gass']), [
                'entries' => [[
                    'program_id' => $programId,
                    'row_id' => $programId,
                    'indicator_id' => $indicatorId,
                    'office_id' => $user->office_id,
                    'year' => 2026,
                    'kind' => 'accomplishment',
                    'jan' => 250,
                    'q1' => 250,
                    'annual_total' => 250,
                    'change_reason' => 'Corrected January disbursement.',
                ]],
            ]);

        $response->assertOk()->assertJson([
            'pending_approval' => true,
            'queued_count' => 1,
        ]);
        $this->assertSame(100.0, FinancialAccomplishment::query()->sole()->jan);

        $submission = AccomplishmentSubmission::query()->sole();
        $this->actingAs($admin)
            ->patch(route('accomplishment-requests.approve', $submission))
            ->assertRedirect();

        $saved = FinancialAccomplishment::query()->sole();
        $this->assertSame(250.0, $saved->jan);
        $this->assertSame(250.0, $saved->q1);
        $this->assertSame(250.0, $saved->annual_total);
    }

    public function test_admin_and_regional_office_can_directly_edit_locked_accomplishments_without_a_request(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-06-15 08:00:00', 'Asia/Manila'));
        [$user, , $programId, $indicatorId, $admin, $regional] = $this->makeApprovalFixtures();

        FinancialAccomplishment::query()->create([
            'sector' => 'gass',
            'user_id' => $user->id,
            'office_id' => $user->office_id,
            'program_id' => $programId,
            'row_id' => $programId,
            'indicator_id' => $indicatorId,
            'year' => 2026,
            'jan' => 100,
            'q1' => 100,
            'annual_total' => 100,
        ]);

        foreach ([[$admin, 200], [$regional, 300]] as [$reviewer, $amount]) {
            $this->actingAs($reviewer)
                ->postJson(route('financial_inputs.store', ['sector' => 'gass']), [
                    'entries' => [[
                        'program_id' => $programId,
                        'row_id' => $programId,
                        'indicator_id' => $indicatorId,
                        'office_id' => $user->office_id,
                        'year' => 2026,
                        'kind' => 'accomplishment',
                        'jan' => $amount,
                        'q1' => $amount,
                        'annual_total' => $amount,
                    ]],
                ])
                ->assertOk()
                ->assertJson(['pending_approval' => false]);
        }

        $this->assertSame(300.0, FinancialAccomplishment::query()->sole()->jan);
        $this->assertDatabaseCount('accomplishment_submissions', 0);
    }

    public function test_regional_office_can_save_a_locked_physical_accomplishment(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-06-15 08:00:00', 'Asia/Manila'));
        [$user, , $programId, $indicatorId, , $regional] = $this->makeApprovalFixtures();

        PhysicalAccomplishment::query()->create([
            'sector' => 'gass',
            'user_id' => $user->id,
            'office_id' => $user->office_id,
            'program_id' => $programId,
            'row_id' => $programId,
            'indicator_id' => $indicatorId,
            'year' => 2026,
            'jan' => 100,
            'q1' => 100,
            'annual_total' => 100,
        ]);

        $this->actingAs($regional)
            ->postJson(route('regional.physical-accomplishments.store', ['sector' => 'gass']), [
                'entries' => [[
                    'program_id' => $programId,
                    'row_id' => $programId,
                    'indicator_id' => $indicatorId,
                    'office_id' => $user->office_id,
                    'year' => 2026,
                    'jan' => 200,
                    'q1' => 200,
                    'annual_total' => 200,
                ]],
            ])
            ->assertOk()
            ->assertJson(['pending_approval' => false]);

        $this->assertSame(200.0, PhysicalAccomplishment::query()->sole()->jan);
        $this->assertDatabaseCount('accomplishment_submissions', 0);
    }

    public function test_user_cannot_call_the_physical_target_save_route(): void
    {
        [$user] = $this->makeApprovalFixtures();

        $this->actingAs($user)
            ->postJson(route('users.gass_physical.targets.store'), ['entries' => []])
            ->assertForbidden();
    }

    public function test_admin_can_decline_but_penro_cannot_review_a_locked_change(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-06-15 08:00:00', 'Asia/Manila'));
        [$user, $penro, $programId, $indicatorId, $admin] = $this->makeApprovalFixtures();

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

        app(AccomplishmentSubmissionService::class)->queueLockedChange($user, 'physical', 'gass', [
            'program_id' => $programId,
            'row_id' => $programId,
            'indicator_id' => $indicatorId,
            'office_id' => $user->office_id,
            'year' => 2026,
            'jan' => 8,
        ], ['jan'], 'The January source document was corrected.');

        $submission = AccomplishmentSubmission::query()->sole();

        $this->actingAs($penro)
            ->patch(route('accomplishment-requests.decline', $submission), [
                'submission_id' => $submission->id,
                'review_notes' => 'PENRO must not be allowed.',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('accomplishment-requests.decline', $submission), [
                'submission_id' => $submission->id,
                'review_notes' => 'Please attach supporting details.',
            ])
            ->assertRedirect(
                route('accomplishment-requests.index', ['status' => 'declined']).'#submission-'.$submission->id
            );

        $this->assertDatabaseHas('accomplishment_submissions', [
            'id' => $submission->id,
            'status' => 'declined',
            'review_notes' => 'Please attach supporting details.',
            'user_read_at' => null,
        ]);
        $this->assertDatabaseCount('physical_accomplishments', 0);

        $this->actingAs($admin)
            ->get(route('accomplishment-requests.index', ['status' => 'declined']))
            ->assertOk()
            ->assertSee('Please attach supporting details.');

        $this->actingAs($user)
            ->get(route('notifications.index', ['status' => 'declined']))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('New')
            ->assertSee('Reason for decline')
            ->assertSee('Please attach supporting details.')
            ->assertSee('System Administrator')
            ->assertSeeInOrder(['Target: 10', 'User input: 8'])
            ->assertDontSee('View entry')
            ->assertDontSee('highlight_period');

        $this->assertNotNull($submission->fresh()->user_read_at);
    }

    private function makeApprovalFixtures(): array
    {
        $now = now();
        $roTypeId = DB::table('office_types')->insertGetId([
            'name' => 'RO', 'desc' => 'Regional Office', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $penroTypeId = DB::table('office_types')->insertGetId([
            'name' => 'PENRO', 'desc' => 'PENRO', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $cenroTypeId = DB::table('office_types')->insertGetId([
            'name' => 'CENRO', 'desc' => 'CENRO', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $roOfficeId = DB::table('offices')->insertGetId([
            'name' => 'CAR', 'office_types_id' => $roTypeId, 'created_at' => $now, 'updated_at' => $now,
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
        $admin = User::query()->create([
            'name' => 'System Administrator',
            'email' => 'admin@example.test',
            'password' => 'Password1!',
            'role' => 'admin',
            'office_id' => $roOfficeId,
        ]);
        $regional = User::query()->create([
            'name' => 'Regional Reviewer',
            'email' => 'regional@example.test',
            'password' => 'Password1!',
            'role' => 'ro-office',
            'office_id' => $roOfficeId,
        ]);

        $this->assertSame($penroOfficeId, Office::penroOfficeIdFor($cenroOfficeId));

        return [$user, $penro, $programId, $indicatorId, $admin, $regional];
    }
}
