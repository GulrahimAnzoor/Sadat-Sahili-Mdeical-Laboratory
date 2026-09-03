<?php

namespace Database\Factories;

use App\Models\Test;
use App\Models\TestParameter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TestParameter>
 */
class TestParameterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'test_id' => Test::factory(),
            'name' => fake()->words(2, true),
            'unit' => fake()->randomElement(['mg/dL', 'g/dL', 'mIU/L', '%']),
            'normal_range' => fake()->numerify('##-##'),
            'group_name' => fake()->optional()->word(),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
