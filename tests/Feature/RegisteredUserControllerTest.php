<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisteredUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsManager = false;

    public function test_first_user_can_register_and_is_stored_as_administrator(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Ahmad Sadat',
                'email' => 'ahmad@ssml.test',
                'password' => 'Secret123',
                'password_confirmation' => 'Secret123',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $user = User::query()->with(['staff.role'])->firstWhere('email', 'ahmad@ssml.test');

        $this->assertNotNull($user);
        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->is_active);
        $this->assertSame('Ahmad Sadat', $user->staff?->name);
        $this->assertSame('manager', $user->staff?->role?->slug);
    }

    public function test_register_is_unavailable_after_the_first_user_exists(): void
    {
        User::factory()->create();

        $this->get(route('register'))
            ->assertRedirect(route('login'));

        $this->post(route('register.store'), [
            'name' => 'Second',
            'email' => 'second@ssml.test',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ])->assertForbidden();

        $this->assertSame(1, User::query()->count());
        $this->assertGuest();
    }

    public function test_registration_rejects_a_weak_password(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Ahmad Sadat',
                'email' => 'ahmad@ssml.test',
                'password' => 'secret',
                'password_confirmation' => 'secret',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['password']);

        $this->assertSame(0, User::query()->count());
        $this->assertSame(0, Staff::query()->count());
        $this->assertNotNull(Role::query()->firstWhere('slug', 'manager'));
    }
}
