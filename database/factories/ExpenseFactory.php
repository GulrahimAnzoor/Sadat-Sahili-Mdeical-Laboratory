<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Rent', 'Electricity', 'Fuel', 'Other']),
            'category' => fake()->randomElement(['rent', 'electricity', 'fuel', 'other']),
            'amount' => fake()->randomFloat(2, 100, 8000),
            'spent_on' => fake()->date(),
        ];
    }
}
