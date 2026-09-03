<?php

namespace Tests\Feature;

use App\Enums\VisitStatus;
use App\Models\CashTransaction;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceptionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_page_renders_compact_form_and_todays_visits(): void
    {
        $response = $this->get(route('reception.index'));

        $response->assertOk();
        $response->assertSee('Reception');
        $response->assertSee('Self request');
        $response->assertSee("Today's visits");
        $response->assertSee('name="age_unit"', false);
        $response->assertSee('data-age-months', false);
    }

    public function test_storing_patient_without_doctor_redirects_to_visit_page(): void
    {
        $response = $this->post(route('patients.store'), [
            'name' => 'Karim',
            'father_name' => 'Najib',
            'gender' => 'male',
            'age' => '32',
            'phone' => '0701234567',
        ]);

        $patient = Patient::query()->firstWhere('name', 'Karim');

        $this->assertNotNull($patient);
        $this->assertNull($patient->doctor_id);
        $response->assertRedirect(route('reception.visit', $patient));
    }

    public function test_storing_visit_applies_discount_records_cash_in_and_opens_dual_tokens(): void
    {
        $patient = Patient::factory()->selfRequest()->create(['name' => 'Amina']);
        $cbc = Test::factory()->create(['name' => 'CBC', 'price' => '100.00']);
        $tft = Test::factory()->create(['name' => 'TFT', 'price' => '50.00']);

        $response = $this->post(route('reception.visits.store', $patient), [
            'test_ids' => [$cbc->id, $tft->id],
            'discount_percent' => '10',
            'paid' => '1',
        ]);

        $visit = Visit::query()->first();

        $this->assertNotNull($visit);
        $this->assertTrue($visit->is_self_request);
        $this->assertSame('150.00', $visit->subtotal);
        $this->assertSame('15.00', $visit->discount_amount);
        $this->assertSame('135.00', $visit->total);
        $this->assertTrue($visit->paid);
        $this->assertSame(2, $visit->patientTests()->count());
        $this->assertSame('135.00', CashTransaction::query()->first()->amount);
        $response->assertRedirect(route('visits.token', $visit));

        $this->get(route('visits.token', $visit))
            ->assertOk()
            ->assertSee('Laboratory copy')
            ->assertSee('Patient copy')
            ->assertSee('Amina')
            ->assertSee('CBC')
            ->assertSee('TFT')
            ->assertSee('Self request');
    }

    public function test_storing_visit_rejects_missing_tests(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->from(route('reception.visit', $patient))
            ->post(route('reception.visits.store', $patient), [
                'discount_percent' => '0',
                'paid' => '0',
            ]);

        $response->assertRedirect(route('reception.visit', $patient));
        $response->assertSessionHasErrors(['test_ids']);
        $this->assertSame(0, Visit::query()->count());
    }

    public function test_visit_page_shows_the_picker_and_save_steps_before_print(): void
    {
        $patient = Patient::factory()->selfRequest()->create();
        Test::factory()->create(['name' => 'CBC', 'price' => '100.00']);

        $this->get(route('reception.visit', $patient))
            ->assertOk()
            ->assertSee('Save tests')
            ->assertSee('Save discount')
            ->assertSee('Choose tests')
            ->assertSee('No tests selected yet. Search, tick, and they will appear here.')
            ->assertDontSee('Tick a test to register it on this visit.')
            ->assertDontSee('Save and print tokens');

        $this->assertSame(0, Visit::query()->count());
    }

    public function test_syncing_selected_tests_saves_them_and_sums_the_visit(): void
    {
        $patient = Patient::factory()->selfRequest()->create();
        $cbc = Test::factory()->create(['name' => 'CBC', 'price' => '100.00']);
        $tft = Test::factory()->create(['name' => 'TFT', 'price' => '50.00']);

        $response = $this->putJson(route('reception.tests.sync', $patient), [
            'test_ids' => [$cbc->id, $tft->id],
        ]);

        $visit = Visit::query()->first();

        $response->assertOk();
        $response->assertJsonPath('subtotal', 150);
        $response->assertJsonPath('total', 150);
        $this->assertNotNull($visit);
        $this->assertSame(2, $visit->patientTests()->count());
        $this->assertSame('150.00', $visit->subtotal);
    }

    public function test_syncing_replaces_the_previous_test_selection(): void
    {
        $patient = Patient::factory()->selfRequest()->create();
        $cbc = Test::factory()->create(['price' => '100.00']);
        $tft = Test::factory()->create(['price' => '50.00']);

        $this->putJson(route('reception.tests.sync', $patient), [
            'test_ids' => [$cbc->id, $tft->id],
        ]);

        $visit = Visit::query()->first();

        $this->putJson(route('reception.tests.sync', $patient), [
            'test_ids' => [$cbc->id],
        ])->assertJsonPath('subtotal', 100);

        $this->assertSame(1, $visit->fresh()->patientTests()->count());
        $this->assertSame('100.00', $visit->fresh()->subtotal);
        $this->assertDatabaseMissing('patient_tests', [
            'visit_id' => $visit->id,
            'test_id' => $tft->id,
        ]);
    }

    public function test_syncing_rejects_an_empty_test_list(): void
    {
        $patient = Patient::factory()->selfRequest()->create();

        $this->putJson(route('reception.tests.sync', $patient), [
            'test_ids' => [],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['test_ids']);

        $this->assertSame(0, Visit::query()->count());
    }

    public function test_syncing_rejects_an_unknown_test_id(): void
    {
        $patient = Patient::factory()->selfRequest()->create();

        $this->putJson(route('reception.tests.sync', $patient), [
            'test_ids' => [999],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['test_ids.0']);

        $this->assertSame(0, Visit::query()->count());
    }

    public function test_selecting_a_test_registers_it_and_sums_the_visit(): void
    {
        $patient = Patient::factory()->selfRequest()->create();
        $cbc = Test::factory()->create(['name' => 'CBC', 'price' => '100.00']);
        $tft = Test::factory()->create(['name' => 'TFT', 'price' => '50.00']);

        $first = $this->postJson(route('reception.tests.store', $patient), [
            'test_id' => $cbc->id,
        ]);

        $first->assertOk();
        $first->assertJsonPath('subtotal', 100);
        $first->assertJsonPath('total', 100);

        $visit = Visit::query()->first();

        $this->assertNotNull($visit);
        $this->assertSame(1, $visit->patientTests()->count());

        $this->postJson(route('reception.tests.store', $patient), [
            'test_id' => $tft->id,
        ])->assertJsonPath('subtotal', 150);

        $this->assertSame(2, $visit->fresh()->patientTests()->count());
        $this->assertSame('150.00', $visit->fresh()->subtotal);
    }

    public function test_discount_recalculates_the_registered_visit_total(): void
    {
        $patient = Patient::factory()->selfRequest()->create();
        $test = Test::factory()->create(['price' => '200.00']);

        $this->postJson(route('reception.tests.store', $patient), [
            'test_id' => $test->id,
        ]);

        $visit = Visit::query()->first();

        $this->patchJson(route('visits.billing.update', $visit), [
            'discount_percent' => '10',
        ])
            ->assertOk()
            ->assertJsonPath('discount_amount', 20)
            ->assertJsonPath('total', 180);

        $this->assertSame('180.00', $visit->fresh()->total);
    }

    public function test_printing_a_paid_visit_records_cash_and_opens_tokens(): void
    {
        $patient = Patient::factory()->selfRequest()->create(['name' => 'Nadia']);
        $test = Test::factory()->create(['name' => 'ESR', 'price' => '80.00']);

        $this->putJson(route('reception.tests.sync', $patient), [
            'test_ids' => [$test->id],
        ]);

        $visit = Visit::query()->first();

        $this->patchJson(route('visits.billing.update', $visit), [
            'discount_percent' => '25',
        ]);

        $response = $this->post(route('visits.print', $visit), [
            'paid' => '1',
            'discount_percent' => '25',
        ]);

        $visit->refresh();

        $this->assertTrue($visit->paid);
        $this->assertSame('60.00', $visit->total);
        $this->assertSame('60.00', CashTransaction::query()->first()->amount);
        $response->assertRedirect(route('visits.token', $visit));
    }

    public function test_printing_rejects_a_visit_without_tests(): void
    {
        $visit = Visit::factory()->selfRequest()->create();

        $response = $this->from(route('reception.visit', $visit->patient))
            ->post(route('visits.print', $visit), [
                'paid' => '1',
            ]);

        $response->assertRedirect(route('reception.visit', $visit->patient));
        $response->assertSessionHasErrors(['test_ids']);
    }

    public function test_visit_with_doctor_keeps_referrer_on_token(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'Dr. Layla']);
        $patient = Patient::factory()->for($doctor)->create(['name' => 'Zahra']);
        $test = Test::factory()->create(['name' => 'Urinalysis', 'price' => '80.00']);

        $this->post(route('reception.visits.store', $patient), [
            'test_ids' => [$test->id],
            'paid' => '0',
        ]);

        $visit = Visit::query()->first();

        $this->assertFalse($visit->is_self_request);
        $this->assertSame($doctor->id, $visit->doctor_id);
        $this->assertSame(VisitStatus::Registered, $visit->status);
        $this->assertSame(0, PatientTest::query()->where('paid', true)->count());
        $this->get(route('visits.token', $visit))->assertSee('Dr. Layla');
    }
}
