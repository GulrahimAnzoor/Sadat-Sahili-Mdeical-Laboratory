<?php

namespace Tests\Feature;

use App\Enums\AgeUnit;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_patients(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'Dr. Layla Ahmadi']);
        Patient::factory()->for($doctor)->create(['name' => 'Zahra']);

        $response = $this->get(route('patients.index'));

        $response->assertOk();
        $response->assertSee('Zahra');
        $response->assertSee('Dr. Layla Ahmadi');
    }

    public function test_store_creates_patient_and_redirects_to_reception_visit(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->post(route('patients.store'), [
            'name' => 'Karim',
            'father_name' => 'Najib',
            'gender' => 'male',
            'description' => 'Chest pain evaluation',
            'doctor_id' => $doctor->id,
        ]);

        $patient = Patient::query()->firstWhere('name', 'Karim');

        $this->assertNotNull($patient);
        $this->assertSame('Najib', $patient->father_name);
        $this->assertSame($doctor->id, $patient->doctor_id);
        $response->assertRedirect(route('reception.visit', $patient));
        $response->assertSessionHas('success', 'Patient saved successfully.');
    }

    public function test_store_allows_self_request_without_doctor(): void
    {
        $response = $this->post(route('patients.store'), [
            'name' => 'Omar',
            'father_name' => 'Salim',
            'gender' => 'male',
        ]);

        $patient = Patient::query()->firstWhere('name', 'Omar');

        $this->assertNotNull($patient);
        $this->assertNull($patient->doctor_id);
        $response->assertRedirect(route('reception.visit', $patient));
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $response = $this->from(route('patients.create'))->post(route('patients.store'), []);

        $response->assertRedirect(route('patients.create'));
        $response->assertSessionHasErrors(['name', 'father_name', 'gender']);
        $this->assertSame(0, Patient::query()->count());
    }

    public function test_update_changes_patient_details(): void
    {
        $patient = Patient::factory()->create(['name' => 'Bilal']);

        $response = $this->put(route('patients.update', $patient), [
            'name' => 'Amina',
            'father_name' => $patient->father_name,
            'gender' => 'female',
            'description' => 'Thyroid monitoring',
            'doctor_id' => $patient->doctor_id,
        ]);

        $response->assertRedirect(route('patients.show', $patient));
        $response->assertSessionHas('success', 'Patient updated successfully.');
        $patient->refresh();
        $this->assertSame('Amina', $patient->name);
        $this->assertSame('female', $patient->gender);
    }

    public function test_destroy_deletes_patient(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->delete(route('patients.destroy', $patient));

        $response->assertRedirect(route('patients.index'));
        $response->assertSessionHas('success', 'Patient deleted.');
        $this->assertModelMissing($patient);
    }

    public function test_store_saves_age_in_years_by_default(): void
    {
        $response = $this->post(route('patients.store'), [
            'name' => 'Karim',
            'father_name' => 'Najib',
            'gender' => 'male',
            'age' => '32',
        ]);

        $patient = Patient::query()->firstWhere('name', 'Karim');

        $this->assertNotNull($patient);
        $this->assertSame(32, $patient->age);
        $this->assertSame(AgeUnit::Years, $patient->age_unit);
        $this->assertSame('32 y', $patient->ageLabel());
        $response->assertRedirect(route('reception.visit', $patient));
    }

    public function test_store_saves_age_in_months_when_unit_is_checked(): void
    {
        $response = $this->post(route('patients.store'), [
            'name' => 'Zahra',
            'father_name' => 'Najib',
            'gender' => 'female',
            'age' => '8',
            'age_unit' => 'm',
        ]);

        $patient = Patient::query()->firstWhere('name', 'Zahra');

        $this->assertNotNull($patient);
        $this->assertSame(8, $patient->age);
        $this->assertSame(AgeUnit::Months, $patient->age_unit);
        $this->assertSame('8 m', $patient->ageLabel());
        $response->assertRedirect(route('reception.visit', $patient));
    }

    public function test_store_rejects_month_age_above_twenty_three(): void
    {
        $response = $this->from(route('reception.index'))->post(route('patients.store'), [
            'name' => 'Omar',
            'father_name' => 'Salim',
            'gender' => 'male',
            'age' => '24',
            'age_unit' => 'm',
        ]);

        $response->assertRedirect(route('reception.index'));
        $response->assertSessionHasErrors(['age' => 'The age field must not be greater than 23.']);
        $this->assertSame(0, Patient::query()->count());
    }

    public function test_show_displays_month_age_with_unit(): void
    {
        $patient = Patient::factory()->monthsOld(8)->create(['name' => 'Amina']);

        $this->get(route('patients.show', $patient))
            ->assertOk()
            ->assertSee('Amina')
            ->assertSee('8 m');
    }

    public function test_update_switches_age_from_years_to_months(): void
    {
        $patient = Patient::factory()->create([
            'age' => 2,
            'age_unit' => AgeUnit::Years,
        ]);

        $response = $this->put(route('patients.update', $patient), [
            'name' => $patient->name,
            'father_name' => $patient->father_name,
            'gender' => $patient->gender,
            'age' => '11',
            'age_unit' => 'm',
            'doctor_id' => $patient->doctor_id,
        ]);

        $response->assertRedirect(route('patients.show', $patient));
        $response->assertSessionHas('success', 'Patient updated successfully.');
        $patient->refresh();
        $this->assertSame(11, $patient->age);
        $this->assertSame(AgeUnit::Months, $patient->age_unit);
        $this->assertSame('11 m', $patient->ageLabel());
    }
}
