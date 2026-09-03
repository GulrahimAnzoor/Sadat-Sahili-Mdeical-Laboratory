<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Reception', 'Manager', 'Director', 'Petty cash']),
            'is_default' => false,
        ];
    }

    public function reception(): static
    {
        return $this->state(fn (): array => [
            'name' => 'Reception',
            'is_default' => true,
        ]);
    }
}
