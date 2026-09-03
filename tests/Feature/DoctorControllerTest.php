<?php

namespace Tests\Feature;

use App\Models\Doctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_doctors(): void
    {
        Doctor::factory()->create(['name' => 'Dr. Fatima Sahili']);

        $response = $this->get(route('doctors.index'));

        $response->assertOk();
        $response->assertSee('Dr. Fatima Sahili');
    }

    public function test_store_creates_doctor_and_redirects_to_show(): void
    {
        $response = $this->post(route('doctors.store'), [
            'name' => 'Dr. Omar Karimi',
        ]);

        $doctor = Doctor::query()->firstWhere('name', 'Dr. Omar Karimi');

        $this->assertNotNull($doctor);
        $response->assertRedirect(route('doctors.show', $doctor));
        $response->assertSessionHas('success', 'Doctor saved successfully.');
    }

    public function test_store_rejects_missing_name(): void
    {
        $response = $this->from(route('doctors.create'))->post(route('doctors.store'), [
            'name' => '',
        ]);

        $response->assertRedirect(route('doctors.create'));
        $response->assertSessionHasErrors('name');
        $this->assertSame(0, Doctor::query()->count());
    }

    public function test_update_changes_doctor_name(): void
    {
        $doctor = Doctor::factory()->create(['name' => 'Dr. Old Name']);

        $response = $this->put(route('doctors.update', $doctor), [
            'name' => 'Dr. New Name',
        ]);

        $response->assertRedirect(route('doctors.show', $doctor));
        $this->assertSame('Dr. New Name', $doctor->fresh()->name);
    }

    public function test_destroy_deletes_doctor(): void
    {
        $doctor = Doctor::factory()->create();

        $response = $this->delete(route('doctors.destroy', $doctor));

        $response->assertRedirect(route('doctors.index'));
        $this->assertModelMissing($doctor);
    }
}
