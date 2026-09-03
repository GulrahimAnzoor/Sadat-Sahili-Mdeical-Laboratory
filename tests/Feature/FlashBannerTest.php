<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Test;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_doctor_shows_the_success_banner_on_the_next_page(): void
    {
        $this->followingRedirects()
            ->post(route('doctors.store'), [
                'name' => 'Dr. Omar Karimi',
            ])
            ->assertOk()
            ->assertSee('id="lab-flash"', false)
            ->assertSee('Doctor saved successfully.');
    }

    public function test_deleting_a_patient_shows_the_success_banner_on_the_index(): void
    {
        $patient = Patient::factory()->create();

        $this->followingRedirects()
            ->delete(route('patients.destroy', $patient))
            ->assertOk()
            ->assertSee('Patient deleted.');
    }

    public function test_saving_visit_tests_returns_a_success_message(): void
    {
        $patient = Patient::factory()->selfRequest()->create();
        $test = Test::factory()->create(['price' => '100.00']);

        $this->putJson(route('reception.tests.sync', $patient), [
            'test_ids' => [$test->id],
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Tests saved.');
    }

    public function test_saving_a_visit_discount_returns_a_success_message(): void
    {
        $patient = Patient::factory()->selfRequest()->create();
        $test = Test::factory()->create(['price' => '200.00']);

        $this->putJson(route('reception.tests.sync', $patient), [
            'test_ids' => [$test->id],
        ]);

        $visit = Visit::query()->first();

        $this->assertNotNull($visit);

        $this->patchJson(route('visits.billing.update', $visit), [
            'discount_percent' => '10',
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Discount saved.');
    }

    public function test_failed_create_shows_that_the_action_could_not_be_completed(): void
    {
        $this->from(route('doctors.create'))
            ->followingRedirects()
            ->post(route('doctors.store'), [
                'name' => '',
            ])
            ->assertOk()
            ->assertSee('The action could not be completed.');
    }
}
