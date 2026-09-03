<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorklistControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_worklist_lists_assignments_without_results(): void
    {
        $patient = Patient::factory()->create(['name' => 'Zahra']);
        $test = Test::factory()->create(['name' => 'TFT']);
        PatientTest::factory()->for($patient)->for($test)->create();

        $response = $this->get(route('worklist'));

        $response->assertOk();
        $response->assertSee('Zahra');
        $response->assertSee('TFT');
        $response->assertSee('Enter results');
        $response->assertSee('Search by name, file no., token or queue');
    }

    public function test_worklist_search_finds_a_patient_and_hides_others(): void
    {
        $match = Patient::factory()->create(['name' => 'Amina']);
        $other = Patient::factory()->create(['name' => 'Karim']);
        PatientTest::factory()->for($match)->create();
        PatientTest::factory()->for($other)->create();

        $response = $this->get(route('worklist', ['q' => 'Amina']));

        $response->assertOk();
        $response->assertSee('Amina');
        $response->assertDontSee('Karim');
    }

    public function test_worklist_patient_card_opens_the_result_page(): void
    {
        $patient = Patient::factory()->create(['name' => 'Nadia', 'file_number' => 'P-00021']);
        $test = Test::factory()->create(['name' => 'CBC']);
        $patientTest = PatientTest::factory()->for($patient)->for($test)->create();
        $visit = $patientTest->fresh()->visit;

        $this->get(route('worklist'))
            ->assertOk()
            ->assertSee(route('visits.results.edit', $visit), false);

        $this->get(route('visits.results.edit', $visit))
            ->assertOk()
            ->assertSee('Nadia')
            ->assertSee('P-00021')
            ->assertSee('CBC')
            ->assertSee('Ordered tests')
            ->assertSee('Save result');
    }
}
