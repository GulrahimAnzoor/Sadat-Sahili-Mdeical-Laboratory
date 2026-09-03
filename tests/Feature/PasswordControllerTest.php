<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_update_their_password(): void
    {
        $this->from(route('password.edit'))
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'Secret123',
                'password_confirmation' => 'Secret123',
            ])
            ->assertRedirect(route('password.edit'));

        $this->assertTrue(Hash::check('Secret123', auth()->user()->fresh()->password));
    }

    public function test_password_update_rejects_an_incorrect_current_password(): void
    {
        $this->from(route('password.edit'))
            ->put(route('password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'Secret123',
                'password_confirmation' => 'Secret123',
            ])
            ->assertRedirect(route('password.edit'))
            ->assertSessionHasErrors(['current_password']);

        $this->assertTrue(Hash::check('password', auth()->user()->fresh()->password));
    }
}
