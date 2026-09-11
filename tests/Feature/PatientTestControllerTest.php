<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_assigned_tests(): void
    {
        $patient = Patient::factory()->create(['name' => 'Maryam']);
        $test = Test::factory()->create(['name' => 'Urinalysis']);
        PatientTest::factory()->for($patient)->for($test)->create();

        $response = $this->get(route('patient-tests.index'));

        $response->assertOk();
        $response->assertSee('Maryam');
        $response->assertSee('Urinalysis');
    }

    public function test_store_assigns_test_and_uses_catalog_price_when_blank(): void
    {
        $patient = Patient::factory()->create();
        $test = Test::factory()->create(['price' => '180.00']);

        $response = $this->post(route('patient-tests.store'), [
            'patient_id' => $patient->id,
            'test_id' => $test->id,
            'total_price' => '',
        ]);

        $patientTest = PatientTest::query()->first();

        $this->assertNotNull($patientTest);
        $this->assertSame('180.00', $patientTest->total_price);
        $this->assertFalse($patientTest->paid);
        $response->assertRedirect(route('patient-tests.show', $patientTest));
    }

    public function test_store_rejects_missing_patient_and_test(): void
    {
        $response = $this->from(route('patient-tests.create'))->post(route('patient-tests.store'), []);

        $response->assertRedirect(route('patient-tests.create'));
        $response->assertSessionHasErrors(['patient_id', 'test_id']);
        $this->assertSame(0, PatientTest::query()->count());
    }

    public function test_update_marks_assignment_as_paid(): void
    {
        $patientTest = PatientTest::factory()->create([
            'paid' => false,
            'total_price' => '100.00',
        ]);

        $response = $this->put(route('patient-tests.update', $patientTest), [
            'patient_id' => $patientTest->patient_id,
            'test_id' => $patientTest->test_id,
            'total_price' => '100.00',
            'paid' => '1',
        ]);

        $response->assertRedirect(route('patient-tests.show', $patientTest));
        $this->assertTrue($patientTest->fresh()->paid);
    }

    public function test_destroy_deletes_assignment(): void
    {
        $patientTest = PatientTest::factory()->create(['paid' => false]);

        $response = $this->delete(route('patient-tests.destroy', $patientTest));

        $response->assertRedirect(route('patient-tests.index'));
        $this->assertModelMissing($patientTest);
    }

    public function test_payment_endpoint_marks_assignment_as_paid(): void
    {
        $patientTest = PatientTest::factory()->create(['paid' => false]);

        $response = $this->from(route('dashboard'))->patch(route('patient-tests.payment', $patientTest));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');
        $this->assertTrue($patientTest->fresh()->paid);
    }

    public function test_store_assigns_multiple_tests_and_redirects_to_patient(): void
    {
        $patient = Patient::factory()->create();
        $cbc = Test::factory()->create(['price' => '150.00']);
        $tft = Test::factory()->create(['price' => '450.00']);

        $response = $this->post(route('patient-tests.store'), [
            'patient_id' => $patient->id,
            'test_ids' => [$cbc->id, $tft->id],
            'paid' => '1',
        ]);

        $this->assertSame(2, PatientTest::query()->count());
        $this->assertSame('150.00', PatientTest::query()->where('test_id', $cbc->id)->value('total_price'));
        $this->assertTrue(PatientTest::query()->where('test_id', $cbc->id)->first()?->paid);
        $response->assertRedirect(route('patients.show', $patient));
    }

    public function test_create_form_prefills_patient_from_query_string(): void
    {
        $patient = Patient::factory()->create(['name' => 'Karim']);

        $response = $this->get(route('patient-tests.create', ['patient_id' => $patient->id]));

        $response->assertOk();
        $response->assertSee('selected', false);
        $response->assertSee('Karim');
    }
}
