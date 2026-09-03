<?php

namespace Database\Factories;

use App\Enums\CashFlow;
use App\Models\Account;
use App\Models\CashTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashTransaction>
 */
class CashTransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'visit_id' => null,
            'expense_id' => null,
            'purchase_id' => null,
            'type' => fake()->randomElement([CashFlow::In, CashFlow::Out]),
            'amount' => fake()->randomFloat(2, 20, 500),
            'description' => fake()->sentence(3),
        ];
    }
}
