<?php

namespace Tests\Feature;

use App\Enums\CashFlow;
use App\Models\CashTransaction;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_posts_cash_out_to_finance_ledger(): void
    {
        $response = $this->post(route('expenses.store'), [
            'title' => 'Electricity',
            'category' => 'electricity',
            'amount' => '75.50',
            'spent_on' => now()->toDateString(),
        ]);

        $expense = Expense::query()->first();

        $this->assertNotNull($expense);
        $response->assertRedirect(route('expenses.show', $expense));
        $this->assertDatabaseHas('cash_transactions', [
            'expense_id' => $expense->id,
            'type' => CashFlow::Out->value,
            'amount' => '75.50',
            'description' => 'Electricity',
        ]);

        $this->get(route('finance.index'))
            ->assertOk()
            ->assertSee('Electricity')
            ->assertSee('75.50');
    }

    public function test_destroy_removes_expense_cash_out(): void
    {
        $this->post(route('expenses.store'), [
            'title' => 'Fuel',
            'category' => 'fuel',
            'amount' => '20.00',
            'spent_on' => now()->toDateString(),
        ]);

        $expense = Expense::query()->first();

        $this->delete(route('expenses.destroy', $expense))
            ->assertRedirect(route('expenses.index'));

        $this->assertModelMissing($expense);
        $this->assertSame(0, CashTransaction::query()->where('expense_id', $expense->id)->count());
    }
}
