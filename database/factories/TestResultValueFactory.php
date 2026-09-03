<?php

namespace Database\Factories;

use App\Models\TestParameter;
use App\Models\TestResult;
use App\Models\TestResultValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TestResultValue>
 */
class TestResultValueFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_result_id' => TestResult::factory(),
            'test_parameter_id' => TestParameter::factory(),
            'value' => fake()->numerify('##.#'),
        ];
    }
}
