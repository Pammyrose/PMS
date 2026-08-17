<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use App\Models\User;
use Tests\TestCase;

class PenroRoleViewTest extends TestCase
{
    public function test_penro_accounts_use_the_dedicated_penro_view_namespace(): void
    {
        $this->actingAs(new User(['role' => 'penro']));

        $controller = new class extends Controller
        {
            public function viewFor(string $view): string
            {
                return $this->roleView($view);
            }
        };

        $this->assertTrue(auth()->user()->isPenro());
        $this->assertFalse(auth()->user()->isUser());
        $this->assertSame('penro.index', $controller->viewFor('index'));
        $this->assertSame('penro.gass.gass_physical', $controller->viewFor('gass.gass_physical'));
    }
}
