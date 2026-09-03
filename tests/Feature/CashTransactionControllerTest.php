<?php

namespace Tests\Feature;

use App\Enums\CashFlow;
use App\Models\Account;
use App\Models\CashTransaction;
use App\Models\Patient;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_in_and_cash_out_appear_on_ledger(): void
    {
        $account = Account::query()->isDefault()->first();

        $this->assertNotNull($account);

        $this->post(route('cash-transactions.store'), [
            'account_id' => $account->id,
            'type' => CashFlow::In->value,
            'amount' => '40.00',
            'description' => 'Opening float',
        ])->assertRedirect();

        $this->from(route('finance.index'))
            ->post(route('cash-transactions.store'), [
                'account_id' => $account->id,
                'type' => CashFlow::Out->value,
                'amount' => '12.50',
                'description' => 'Taxi',
            ])
            ->assertRedirect(route('finance.index'));

        $this->assertSame(2, CashTransaction::query()->count());

        $this->get(route('finance.index'))
            ->assertOk()
            ->assertSee('Opening')
            ->assertSee('Closing')
            ->assertSee('Taxi')
            ->assertSee('CREDIT')
            ->assertSee('DEBIT');
    }

    public function test_creating_account_saves_named_till(): void
    {
        $this->from(route('finance.index'))
            ->post(route('accounts.store'), ['name' => 'Manager'])
            ->assertRedirect(route('finance.index'));

        $this->assertTrue(Account::query()->where('name', 'Manager')->exists());
        $this->get(route('finance.index'))->assertSee('Manager');
    }

    public function test_paid_reception_visit_creates_automatic_cash_in(): void
    {
        $patient = Patient::factory()->create();
        $test = Test::factory()->create(['price' => '200.00']);

        $this->post(route('reception.visits.store', $patient), [
            'test_ids' => [$test->id],
            'paid' => '1',
        ]);

        $this->assertTrue(CashTransaction::query()->where('type', CashFlow::In)->where('amount', '200.00')->exists());
        $this->get(route('dashboard'))->assertSee('200.00');
        $this->get(route('finance.index'))->assertSee('200.00');
    }
}
