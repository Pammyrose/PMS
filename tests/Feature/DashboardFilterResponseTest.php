<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFilterResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_results_require_authentication(): void
    {
        $this->get('/dashboard?year=2026', ['X-Dashboard-Partial' => '1'])->assertRedirect('/');
        $this->get('/dashboard', ['X-Dashboard-List' => 'pap'])->assertRedirect('/');
        $this->get('/dashboard', ['X-Dashboard-List' => 'indicator'])->assertRedirect('/');
    }

    public function test_every_role_can_filter_results_without_reloading_the_page(): void
    {
        foreach (['admin', 'ro-office', 'penro', 'user'] as $role) {
            $user = User::query()->create([
                'name' => 'Dropdown tester',
                'email' => $role.'@example.test',
                'password' => 'Password1!',
                'role' => $role,
            ]);
            $this->actingAs($user);
            $this->get('/dashboard?year=2025&sector=gass', ['X-Dashboard-Partial' => '1'])
                ->assertOk()
                ->assertSee('dashboardFilterForm')
                ->assertSee('dashboardComparisonData')
                ->assertSee('dashboardTrendData')
                ->assertSee('2025')
                ->assertDontSee('<html', false)
                ->assertDontSee('components.sidebar');
            $this->get('/dashboard')->assertOk()->assertSee('js/dashboard-filters.js');
            foreach (['pap', 'indicator'] as $list) {
                $this->get('/dashboard?year=2025&sector=gass', ['X-Dashboard-List' => $list])
                    ->assertOk()
                    ->assertSee('No '.($list === 'pap' ? 'PAP' : 'indicator').' records found')
                    ->assertDontSee('dashboardFilterForm')
                    ->assertDontSee('<html', false);
            }
        }
    }
}
