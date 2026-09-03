<?php

namespace Tests\Feature;

use App\Enums\CashFlow;
use App\Models\CashTransaction;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitPaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_paying_unpaid_visit_posts_lab_income_to_dashboard_and_finance(): void
    {
        $patient = Patient::factory()->create(['name' => 'Laila']);
        $test = Test::factory()->create(['name' => 'TSH']);
        $patientTest = PatientTest::factory()->for($patient)->for($test)->create([
            'paid' => false,
            'total_price' => '80.00',
        ]);
        $visit = $patientTest->fresh()->visit;

        $this->from(route('dashboard'))
            ->patch(route('visits.payment', $visit))
            ->assertRedirect(route('dashboard'));

        $this->assertTrue($visit->fresh()->paid);
        $this->assertDatabaseHas('cash_transactions', [
            'visit_id' => $visit->id,
            'type' => CashFlow::In->value,
            'amount' => '80.00',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('80.00');

        $this->get(route('finance.index'))
            ->assertOk()
            ->assertSee('80.00');
    }

    public function test_paying_the_same_visit_twice_does_not_duplicate_cash_in(): void
    {
        $patientTest = PatientTest::factory()->create([
            'paid' => false,
            'total_price' => '50.00',
        ]);
        $visit = $patientTest->fresh()->visit;

        $this->patch(route('visits.payment', $visit));
        $this->patch(route('visits.payment', $visit));

        $this->assertSame(1, CashTransaction::query()->where('visit_id', $visit->id)->count());
        $this->assertSame('50.00', CashTransaction::query()->where('visit_id', $visit->id)->value('amount'));
    }
}
