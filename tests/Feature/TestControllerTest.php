<?php

namespace Tests\Feature;

use App\Models\PatientTest;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_tests(): void
    {
        Test::factory()->create(['name' => 'Complete Blood Count (CBC)']);

        $response = $this->get(route('tests.index'));

        $response->assertOk();
        $response->assertSee('Complete Blood Count (CBC)');
    }

    public function test_store_creates_test_and_redirects_to_show(): void
    {
        $response = $this->post(route('tests.store'), [
            'name' => 'HbA1c',
            'price' => '200.00',
            'normal_range' => '4.0-5.6 %',
        ]);

        $test = Test::query()->firstWhere('name', 'HbA1c');

        $this->assertNotNull($test);
        $this->assertSame('200.00', $test->price);
        $response->assertRedirect(route('tests.show', $test));
    }

    public function test_store_rejects_missing_required_fields(): void
    {
        $response = $this->from(route('tests.create'))->post(route('tests.store'), []);

        $response->assertRedirect(route('tests.create'));
        $response->assertSessionHasErrors(['name', 'price', 'normal_range']);
        $this->assertSame(0, Test::query()->count());
    }

    public function test_update_changes_test_details(): void
    {
        $test = Test::factory()->create([
            'name' => 'Old Test',
            'price' => '50.00',
            'normal_range' => '1-2',
        ]);

        $response = $this->put(route('tests.update', $test), [
            'name' => 'Updated Test',
            'price' => '75.50',
            'normal_range' => '10-20',
        ]);

        $response->assertRedirect(route('tests.show', $test));
        $test->refresh();
        $this->assertSame('Updated Test', $test->name);
        $this->assertSame('75.50', $test->price);
        $this->assertSame('10-20', $test->normal_range);
    }

    public function test_store_accepts_a_multiline_reference_range(): void
    {
        $range = "Serum/Plasma:\nAdult Fasting: 70-126 mg/dl\nChildren: 60-110 mg/dl";

        $response = $this->post(route('tests.store'), [
            'name' => 'B. Sugar (F)',
            'price' => '80.00',
            'normal_range' => $range,
        ]);

        $test = Test::query()->firstWhere('name', 'B. Sugar (F)');

        $this->assertNotNull($test);
        $this->assertSame($range, $test->normal_range);
        $response->assertRedirect(route('tests.show', $test));
    }

    public function test_destroy_deletes_test(): void
    {
        $test = Test::factory()->create();

        $response = $this->delete(route('tests.destroy', $test));

        $response->assertRedirect(route('tests.index'));
        $this->assertModelMissing($test);
    }

    public function test_destroy_deletes_a_test_with_parameters(): void
    {
        $test = Test::factory()->create();
        $parameter = $test->parameters()->create([
            'name' => 'Hb',
            'unit' => 'g/dL',
            'normal_range' => '12-16',
        ]);

        $response = $this->delete(route('tests.destroy', $test));

        $response->assertRedirect(route('tests.index'));
        $this->assertModelMissing($test);
        $this->assertDatabaseMissing('test_parameters', ['id' => $parameter->id]);
    }

    public function test_destroy_removes_unpaid_visit_assignments(): void
    {
        $test = Test::factory()->create();
        $patientTest = PatientTest::factory()->for($test)->create([
            'paid' => false,
        ]);

        $response = $this->delete(route('tests.destroy', $test));

        $response->assertRedirect(route('tests.index'));
        $this->assertModelMissing($test);
        $this->assertModelMissing($patientTest);
    }
}
