<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\TestResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_with_tests_cannot_be_deleted(): void
    {
        $patientTest = PatientTest::factory()->create();
        $patient = $patientTest->patient;

        $this->from(route('patients.index'))
            ->delete(route('patients.destroy', $patient))
            ->assertRedirect(route('patients.index'))
            ->assertSessionHasErrors('patient');

        $this->assertModelExists($patient);
        $this->assertModelExists($patientTest);
    }

    public function test_test_used_on_a_visit_cannot_be_deleted(): void
    {
        $patientTest = PatientTest::factory()->create(['paid' => true]);
        $test = $patientTest->test;

        $this->from(route('tests.index'))
            ->delete(route('tests.destroy', $test))
            ->assertRedirect(route('tests.index'))
            ->assertSessionHasErrors('test');

        $this->assertModelExists($test);
        $this->assertModelExists($patientTest);
    }

    public function test_test_with_a_recorded_result_cannot_be_deleted(): void
    {
        $result = TestResult::factory()->create();
        $test = $result->test;

        $this->from(route('tests.index'))
            ->delete(route('tests.destroy', $test))
            ->assertRedirect(route('tests.index'))
            ->assertSessionHasErrors('test');

        $this->assertModelExists($test);
        $this->assertModelExists($result);
    }

    public function test_doctor_linked_to_a_patient_cannot_be_deleted(): void
    {
        $doctor = Doctor::factory()->create();
        Patient::factory()->for($doctor)->create();

        $this->from(route('doctors.index'))
            ->delete(route('doctors.destroy', $doctor))
            ->assertRedirect(route('doctors.index'))
            ->assertSessionHasErrors('doctor');

        $this->assertModelExists($doctor);
    }

    public function test_visit_totals_paid_and_remaining_stay_consistent(): void
    {
        $patient = Patient::factory()->create();
        $alpha = Test::factory()->create(['price' => '100.00']);
        $beta = Test::factory()->create(['price' => '200.00']);
        $gamma = Test::factory()->create(['price' => '150.00']);

        $this->putJson(route('reception.tests.sync', $patient), [
            'test_ids' => [$alpha->id, $beta->id, $gamma->id],
        ])->assertOk()->assertJsonPath('total', 450);

        $visit = $patient->visits()->first();
        $this->assertNotNull($visit);
        $this->assertSame('450.00', $visit->fresh()->total);
        $this->assertSame(450.0, $visit->fresh()->remainingAmount());

        $first = $visit->patientTests()->where('test_id', $alpha->id)->first();
        $second = $visit->patientTests()->where('test_id', $beta->id)->first();

        $this->patch(route('patient-tests.payment', $first))->assertRedirect();
        $this->patch(route('patient-tests.payment', $second))->assertRedirect();

        $visit->refresh();

        $this->assertSame('300.00', $visit->paid_amount);
        $this->assertSame(150.0, $visit->remainingAmount());
        $this->assertFalse($visit->paid);

        $this->patch(route('visits.payment', $visit))->assertRedirect();

        $visit->refresh();

        $this->assertTrue($visit->paid);
        $this->assertSame('450.00', $visit->paid_amount);
        $this->assertSame(0.0, $visit->remainingAmount());
        $this->assertSame(450.0, round((float) $visit->cashTransactions()->sum('amount'), 2));
    }
}
