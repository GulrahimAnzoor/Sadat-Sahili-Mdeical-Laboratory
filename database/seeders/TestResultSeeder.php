<?php

namespace Database\Seeders;

use App\Models\PatientTest;
use App\Models\TestResult;
use Illuminate\Database\Seeder;

class TestResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleResults = [
            'Complete Blood Count (CBC)' => ['result' => '7.2', 'unit' => 'x10^3/µL'],
            'Fasting Blood Sugar' => ['result' => '92', 'unit' => 'mg/dL'],
            'HbA1c' => ['result' => '5.4', 'unit' => '%'],
            'Lipid Profile' => ['result' => '88', 'unit' => 'mg/dL'],
            'Liver Function Test (LFT)' => ['result' => '32', 'unit' => 'U/L'],
            'Kidney Function Test (KFT)' => ['result' => '0.9', 'unit' => 'mg/dL'],
            'Thyroid Stimulating Hormone (TSH)' => ['result' => '2.1', 'unit' => 'mIU/L'],
            'Urinalysis' => ['result' => 'Negative', 'unit' => '-'],
            'C-Reactive Protein (CRP)' => ['result' => '3.2', 'unit' => 'mg/L'],
            'Vitamin D (25-OH)' => ['result' => '24', 'unit' => 'ng/mL'],
        ];

        foreach (PatientTest::query()->with('test')->get() as $patientTest) {
            $sample = $sampleResults[$patientTest->test->name] ?? [
                'result' => (string) fake()->randomFloat(1, 1, 20),
                'unit' => '-',
            ];

            TestResult::query()->create([
                'patient_id' => $patientTest->patient_id,
                'test_id' => $patientTest->test_id,
                'result' => $sample['result'],
                'unit' => $sample['unit'],
            ]);
        }
    }
}
