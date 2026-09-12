<?php

namespace Tests\Feature;

use App\Enums\CashFlow;
use App\Enums\LabPermission;
use App\Enums\StockMovementType;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Test;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePeriodReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        auth()->logout();

        $this->get(route('finance.period-report'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_without_finance_permission_cannot_open_period_report(): void
    {
        $user = $this->staffUser([
            LabPermission::Dashboard->value,
            LabPermission::Reception->value,
        ]);

        $this->actingAs($user)
            ->get(route('finance.period-report'))
            ->assertForbidden();
    }

    public function test_today_report_lists_period_money_and_consumption_with_totals(): void
    {
        $patient = Patient::factory()->create(['name' => 'Ahmad Karimi']);
        $test = Test::factory()->create(['name' => 'CBC Panel']);
        $visit = Visit::factory()->for($patient)->paid()->create([
            'subtotal' => 150,
            'total' => 150,
            'paid_amount' => 150,
        ]);
        PatientTest::factory()->for($visit)->for($patient)->for($test)->create([
            'paid' => true,
            'total_price' => 150,
        ]);

        $oldVisit = Visit::factory()->create();
        $oldVisit->forceFill([
            'paid' => true,
            'paid_amount' => 8888.50,
            'total' => 8888.50,
            'updated_at' => now()->subMonth(),
        ])->saveQuietly();

        Purchase::factory()->create([
            'bill_number' => 'BILL-TODAY-1',
            'billed_on' => now()->toDateString(),
            'subtotal' => 80,
            'received' => 80,
            'remaining' => 0,
        ]);
        Purchase::factory()->create([
            'bill_number' => 'BILL-OLD-9',
            'billed_on' => now()->subMonth()->toDateString(),
            'subtotal' => 5000,
        ]);

        Expense::factory()->create([
            'title' => 'Fuel today',
            'amount' => 25,
            'spent_on' => now()->toDateString(),
        ]);
        Expense::factory()->create([
            'title' => 'Old rent',
            'amount' => 7000,
            'spent_on' => now()->subMonth()->toDateString(),
        ]);

        $item = InventoryItem::factory()->create([
            'name' => 'EDTA tube',
            'unit_cost' => 10,
        ]);
        StockMovement::factory()->create([
            'inventory_item_id' => $item->id,
            'type' => StockMovementType::OutUsage,
            'quantity' => 2,
            'occurred_on' => now()->toDateString(),
            'user_id' => User::query()->where('email', 'manager@ssml.test')->value('id'),
        ]);
        StockMovement::factory()->create([
            'inventory_item_id' => $item->id,
            'type' => StockMovementType::OutUsage,
            'quantity' => 99,
            'occurred_on' => now()->subMonth()->toDateString(),
            'user_id' => User::query()->where('email', 'manager@ssml.test')->value('id'),
        ]);

        CashTransaction::factory()->create([
            'type' => CashFlow::Out,
            'amount' => 40,
            'description' => 'Director withdrawal',
        ]);

        $this->get(route('finance.period-report', ['period' => 'today']))
            ->assertSee('Period report')
            ->assertSee('Ahmad Karimi')
            ->assertSee('CBC Panel')
            ->assertSee('150.00')
            ->assertSee('BILL-TODAY-1')
            ->assertSee('Fuel today')
            ->assertSee('EDTA tube')
            ->assertSee('Lab Manager')
            ->assertSee('Director withdrawal')
            ->assertSee('20.00')
            ->assertSee('Grand totals')
            ->assertDontSee('BILL-OLD-9')
            ->assertDontSee('Old rent')
            ->assertDontSee('8888.50');
    }

    public function test_finance_page_links_to_the_period_report(): void
    {
        $this->get(route('finance.index'))
            ->assertSee(route('finance.period-report', ['period' => 'month'], false));
    }
}
