<?php

namespace Database\Factories;

use App\Enums\TestDepartment;
use App\Models\Test;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Test>
 */
class TestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'price' => fake()->randomFloat(2, 50, 500),
            'normal_range' => fake()->numerify('##-##'),
            'department' => TestDepartment::Routine,
            'is_active' => true,
        ];
    }
}
