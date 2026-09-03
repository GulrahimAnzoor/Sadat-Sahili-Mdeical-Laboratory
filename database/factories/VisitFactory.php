<?php

namespace Database\Factories;

use App\Enums\VisitStatus;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use App\Support\CashLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 800);

        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'is_self_request' => false,
            'subtotal' => $subtotal,
            'discount_percent' => 0,
            'discount_amount' => 0,
            'total' => $subtotal,
            'paid_amount' => 0,
            'paid' => false,
            'status' => VisitStatus::Registered,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Visit $visit): void {
            if (! $visit->paid || (float) $visit->paid_amount <= 0) {
                return;
            }

            app(CashLedger::class)->recordVisitPayment($visit);
        });
    }

    public function selfRequest(): static
    {
        return $this->state(fn (): array => [
            'doctor_id' => null,
            'is_self_request' => true,
        ]);
    }

    public function paid(): static
    {
        return $this->state(function (array $attributes): array {
            $total = $attributes['total'] ?? $attributes['subtotal'] ?? 100;

            return [
                'paid' => true,
                'paid_amount' => $total,
                'status' => VisitStatus::Paid,
            ];
        });
    }
}
