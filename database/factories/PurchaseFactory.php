<?php

namespace Database\Factories;

use App\Enums\PurchaseType;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 200, 5000);
        $received = fake()->randomFloat(2, 0, $subtotal);

        return [
            'supplier_id' => Supplier::factory(),
            'bill_number' => fake()->unique()->numerify('BILL-####'),
            'billed_on' => fake()->date(),
            'type' => PurchaseType::Simple,
            'previous_balance' => 0,
            'subtotal' => $subtotal,
            'received' => $received,
            'remaining' => $subtotal - $received,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
