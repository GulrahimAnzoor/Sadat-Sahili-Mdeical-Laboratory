<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'category' => null,
            'quantity' => fake()->randomFloat(2, 5, 80),
            'min_quantity' => 5,
            'unit_cost' => fake()->randomFloat(2, 10, 200),
            'batch_number' => fake()->optional()->bothify('B##??'),
            'expires_on' => null,
        ];
    }
}
