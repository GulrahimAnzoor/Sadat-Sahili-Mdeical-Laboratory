<?php

namespace Database\Factories;

use App\Enums\VisitStatus;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\Visit;
use App\Support\CashLedger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientTest>
 */
class PatientTestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'test_id' => Test::factory(),
            'total_price' => fake()->randomFloat(2, 50, 800),
            'paid' => fake()->boolean(),
            'status' => VisitStatus::Registered,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PatientTest $patientTest): void {
            if ($patientTest->visit_id !== null) {
                return;
            }

            $patient = Patient::query()->find($patientTest->patient_id);

            $visit = Visit::query()->create([
                'patient_id' => $patientTest->patient_id,
                'doctor_id' => $patient?->doctor_id,
                'is_self_request' => $patient?->doctor_id === null,
                'subtotal' => $patientTest->total_price,
                'total' => $patientTest->total_price,
                'paid_amount' => $patientTest->paid ? $patientTest->total_price : 0,
                'paid' => (bool) $patientTest->paid,
                'status' => $patientTest->paid ? VisitStatus::Paid : VisitStatus::Registered,
            ]);

            $patientTest->updateQuietly(['visit_id' => $visit->id]);

            if ($patientTest->paid) {
                app(CashLedger::class)->recordVisitPayment($visit->fresh());
            }
        });
    }
}
