<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\TestResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestResultControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_results(): void
    {
        $patient = Patient::factory()->create(['name' => 'Nadia']);
        $test = Test::factory()->create(['name' => 'Vitamin D (25-OH)']);
        TestResult::factory()->for($patient)->for($test)->create([
            'result' => '24',
            'unit' => 'ng/mL',
        ]);

        $response = $this->get(route('test-results.index'));

        $response->assertOk();
        $response->assertSee('Nadia');
        $response->assertSee('Vitamin D (25-OH)');
        $response->assertSee('24');
    }

    public function test_store_records_result_for_assigned_test(): void
    {
        $patient = Patient::factory()->create();
        $test = Test::factory()->create();
        PatientTest::factory()->for($patient)->for($test)->create();

        $response = $this->post(route('test-results.store'), [
            'patient_id' => $patient->id,
            'test_id' => $test->id,
            'result' => '92',
            'unit' => 'mg/dL',
        ]);

        $testResult = TestResult::query()->first();

        $this->assertNotNull($testResult);
        $this->assertSame('92', $testResult->result);
        $this->assertSame('mg/dL', $testResult->unit);
        $response->assertRedirect(route('test-results.show', $testResult));
    }

    public function test_store_rejects_result_when_test_was_not_assigned(): void
    {
        $patient = Patient::factory()->create();
        $test = Test::factory()->create();

        $response = $this->from(route('test-results.create'))->post(route('test-results.store'), [
            'patient_id' => $patient->id,
            'test_id' => $test->id,
            'result' => '92',
            'unit' => 'mg/dL',
        ]);

        $response->assertRedirect(route('test-results.create'));
        $response->assertSessionHasErrors([
            'test_id' => 'This test has not been assigned to this patient.',
        ]);
        $this->assertSame(0, TestResult::query()->count());
    }

    public function test_destroy_deletes_result(): void
    {
        $testResult = TestResult::factory()->create();

        $response = $this->delete(route('test-results.destroy', $testResult));

        $response->assertRedirect(route('test-results.index'));
        $this->assertModelMissing($testResult);
    }
}
