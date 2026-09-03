<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_to_dark_theme_persists_on_the_next_page(): void
    {
        $this->from(route('dashboard'))
            ->get(route('theme.switch', 'dark'))
            ->assertRedirect(route('dashboard'));

        $this->assertSame('dark', session('theme'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-theme="dark"', false)
            ->assertSee('aria-current="true"', false);
    }

    public function test_switching_to_system_theme_is_stored(): void
    {
        $this->from(route('dashboard'))
            ->get(route('theme.switch', 'system'))
            ->assertRedirect(route('dashboard'));

        $this->assertSame('system', session('theme'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-theme="system"', false);
    }

    public function test_unknown_theme_returns_404(): void
    {
        $this->get(route('theme.switch', 'neon'))
            ->assertNotFound();
    }
}
