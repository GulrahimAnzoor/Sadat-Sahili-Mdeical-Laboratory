<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\Test;
use App\Models\TestResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TestResult>
 */
class TestResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'test_id' => Test::factory(),
            'result' => fake()->numerify('##.#'),
            'unit' => fake()->randomElement(['mg/dL', 'g/dL', 'mIU/L', '%', 'ng/mL']),
        ];
    }
}
