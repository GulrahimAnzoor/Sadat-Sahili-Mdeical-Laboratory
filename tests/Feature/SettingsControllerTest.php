<?php

namespace Tests\Feature;

use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_hub_lists_doctors_tests_roles_and_accounts(): void
    {
        $response = $this->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('Laboratory settings');
        $response->assertSee('Doctors');
        $response->assertSee('Tests');
        $response->assertSee('Report ranges and summaries');
        $response->assertSee('Staff and roles');
        $response->assertSee('Cash accounts');
        $response->assertSee('Item names');
        $response->assertSee('Open page');
        $response->assertSee('Upload Word / PDF');
        $response->assertDontSee('Store and costs');
    }

    public function test_settings_accounts_page_creates_and_updates_a_till(): void
    {
        $this->get(route('settings.accounts.index'))
            ->assertOk()
            ->assertSee('Cash accounts');

        $response = $this->from(route('settings.accounts.index'))
            ->post(route('accounts.store'), [
                'name' => 'Manager',
            ]);

        $response->assertRedirect(route('settings.accounts.index'));

        $account = Account::query()->firstWhere('name', 'Manager');

        $this->assertNotNull($account);

        $this->put(route('accounts.update', $account), [
            'name' => 'Director',
            'is_default' => '1',
        ])->assertRedirect(route('settings.accounts.index'));

        $this->assertSame('Director', $account->fresh()->name);
        $this->assertTrue($account->fresh()->is_default);
    }
}
