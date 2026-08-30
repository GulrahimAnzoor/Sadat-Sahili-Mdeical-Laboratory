<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientTest>
 */
class PatientTestFactory extends Factory
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
            'total_price' => fake()->randomFloat(2, 50, 800),
            'paid' => fake()->boolean(),
        ];
    }
}
