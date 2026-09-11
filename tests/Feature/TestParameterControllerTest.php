<?php

namespace Tests\Feature;

use App\Models\Test;
use App\Models\TestParameter;
use App\Models\TestResultValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestParameterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_adds_template_row_to_test(): void
    {
        $test = Test::factory()->create(['name' => 'FBS']);

        $response = $this->post(route('tests.parameters.store', $test), [
            'name' => 'B. Sugar (F)',
            'unit' => 'mg/dL',
            'normal_range' => '70-100',
            'group_name' => '',
        ]);

        $response->assertRedirect(route('tests.show', $test));
        $this->assertDatabaseHas('test_parameters', [
            'test_id' => $test->id,
            'name' => 'B. Sugar (F)',
            'unit' => 'mg/dL',
        ]);
    }

    public function test_destroy_removes_template_row(): void
    {
        $test = Test::factory()->create();
        $parameter = TestParameter::factory()->for($test)->create(['name' => 'TSH']);

        $response = $this->delete(route('tests.parameters.destroy', [$test, $parameter]));

        $response->assertRedirect(route('tests.show', $test));
        $this->assertModelMissing($parameter);
    }

    public function test_destroy_rejects_a_parameter_used_on_a_result(): void
    {
        $test = Test::factory()->create();
        $parameter = TestParameter::factory()->for($test)->create(['name' => 'WBC']);
        TestResultValue::factory()->for($parameter, 'parameter')->create();

        $response = $this->from(route('tests.show', $test))
            ->delete(route('tests.parameters.destroy', [$test, $parameter]));

        $response->assertRedirect(route('tests.show', $test));
        $response->assertSessionHasErrors('parameter');
        $this->assertModelExists($parameter);
    }
}
