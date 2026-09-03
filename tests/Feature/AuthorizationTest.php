<?php

namespace Tests\Feature;

use App\Enums\LabPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsManager = false;

    public function test_guests_cannot_open_staff_settings(): void
    {
        $this->get(route('settings.staff.index'))
            ->assertRedirect(route('login'));
    }

    public function test_reception_staff_can_open_reception_and_cannot_open_finance(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Reception->value,
        ]);

        $this->actingAs($user)
            ->get(route('reception.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('finance.index'))
            ->assertForbidden();
    }

    public function test_staff_without_staff_permission_cannot_create_roles(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
        ]);

        $this->actingAs($user)
            ->post(route('settings.roles.store'), [
                'name' => 'Night shift',
            ])
            ->assertForbidden();
    }

    public function test_sidebar_hides_links_the_role_cannot_open(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Reception->value,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Reception')
            ->assertDontSee('/finance')
            ->assertDontSee('/settings');
    }
}
