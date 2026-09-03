<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Role;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Staff>
 */
class StaffFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'phone' => fake()->optional()->numerify('07########'),
            'role_id' => Role::factory(),
            'account_id' => null,
        ];
    }

    public function forAccount(?Account $account = null): static
    {
        return $this->state(fn (): array => [
            'account_id' => $account?->id ?? Account::factory(),
        ]);
    }
}
