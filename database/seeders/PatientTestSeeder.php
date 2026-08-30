<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Test;
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

            foreach ($assignedTests as $test) {
                PatientTest::query()->create([
                    'patient_id' => $patient->id,
                    'test_id' => $test->id,
                    'total_price' => $test->price,
                    'paid' => fake()->boolean(70),
                ]);
            }
        }
    }
}
