<?php

namespace Database\Seeders;

use App\Enums\VisitStatus;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
use App\Models\Visit;
use Illuminate\Database\Seeder;

class PatientTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::query()->get();
        $tests = Test::query()->get();

        foreach ($patients as $patient) {
            $assignedTests = $tests->random(fake()->numberBetween(2, 4));
            $subtotal = (float) $assignedTests->sum('price');
            $paid = fake()->boolean(70);

            $visit = Visit::query()->create([
                'patient_id' => $patient->id,
                'doctor_id' => $patient->doctor_id,
                'is_self_request' => $patient->doctor_id === null,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'paid_amount' => $paid ? $subtotal : 0,
                'paid' => $paid,
                'status' => $paid ? VisitStatus::Paid : VisitStatus::Registered,
            ]);

            foreach ($assignedTests as $test) {
                PatientTest::query()->create([
                    'visit_id' => $visit->id,
                    'patient_id' => $patient->id,
                    'test_id' => $test->id,
                    'total_price' => $test->price,
                    'paid' => $paid,
                    'status' => $paid ? VisitStatus::Paid : VisitStatus::Registered,
                ]);
            }
        }
    }
}
