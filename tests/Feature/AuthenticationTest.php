<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $authenticateAsManager = false;

    public function test_guests_are_redirected_from_the_dashboard_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_login_page_shows_sign_up_when_no_users_exist(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Log in')
            ->assertSee('Sign in')
            ->assertSee('Create administrator')
            ->assertSee('Sign up')
            ->assertSee('AnzoorLab')
            ->assertSee('Gulrahim Anzoor')
            ->assertSee('data-password-toggle', false)
            ->assertSee('English')
            ->assertSee('System');
    }

    public function test_login_page_hides_sign_up_when_users_exist(): void
    {
        User::factory()->create();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Log in')
            ->assertSee('AnzoorLab')
            ->assertDontSee('Create administrator');
    }

    public function test_users_can_authenticate_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'sara@ssml.test',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'sara@ssml.test',
                'password' => 'password',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'sara@ssml.test',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'sara@ssml.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        User::factory()->inactive()->create([
            'email' => 'closed@ssml.test',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'closed@ssml.test',
                'password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_too_many_failures(): void
    {
        User::factory()->create([
            'email' => 'sara@ssml.test',
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->from(route('login'))->post(route('login.store'), [
                'email' => 'sara@ssml.test',
                'password' => 'wrong-password',
            ]);
        }

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'sara@ssml.test',
                'password' => 'wrong-password',
            ])
            ->assertSessionHasErrors(['email']);
    }

    public function test_authenticated_users_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_remember_me_sets_the_remember_cookie(): void
    {
        $user = User::factory()->create([
            'email' => 'sara@ssml.test',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'sara@ssml.test',
                'password' => 'password',
                'remember' => '1',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertCookie(auth()->guard()->getRecallerName());

        $this->assertAuthenticatedAs($user);
    }
}
