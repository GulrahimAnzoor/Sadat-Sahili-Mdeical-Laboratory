<?php

namespace Database\Factories;

use App\Enums\Sensitivity;
use App\Models\CultureSensitivity;
use App\Models\TestResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CultureSensitivity>
 */
class CultureSensitivityFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_result_id' => TestResult::factory(),
            'antibiotic' => fake()->randomElement(['Amoxicillin', 'Ciprofloxacin', 'Gentamicin', 'Ceftriaxone']),
            'sensitivity' => fake()->randomElement(Sensitivity::cases()),
            'sort_order' => 0,
        ];
    }
}
