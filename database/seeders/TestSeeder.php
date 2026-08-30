<?php

namespace Database\Seeders;

use App\Models\Test;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tests = [
            ['name' => 'Complete Blood Count (CBC)', 'price' => 150.00, 'normal_range' => '4.5-11.0 x10^3/µL'],
            ['name' => 'Fasting Blood Sugar', 'price' => 80.00, 'normal_range' => '70-100 mg/dL'],
            ['name' => 'HbA1c', 'price' => 200.00, 'normal_range' => '4.0-5.6 %'],
            ['name' => 'Lipid Profile', 'price' => 250.00, 'normal_range' => 'LDL <100 mg/dL'],
            ['name' => 'Liver Function Test (LFT)', 'price' => 300.00, 'normal_range' => 'ALT 7-56 U/L'],
            ['name' => 'Kidney Function Test (KFT)', 'price' => 280.00, 'normal_range' => 'Creatinine 0.6-1.3 mg/dL'],
            ['name' => 'Thyroid Stimulating Hormone (TSH)', 'price' => 180.00, 'normal_range' => '0.4-4.0 mIU/L'],
            ['name' => 'Urinalysis', 'price' => 100.00, 'normal_range' => 'Negative'],
            ['name' => 'C-Reactive Protein (CRP)', 'price' => 120.00, 'normal_range' => '<5 mg/L'],
            ['name' => 'Vitamin D (25-OH)', 'price' => 350.00, 'normal_range' => '30-100 ng/mL'],
        ];

        foreach ($tests as $test) {
            Test::query()->create($test);
        }
    }
}
