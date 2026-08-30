<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctors = Doctor::query()->pluck('id');

        $patients = [
            ['name' => 'Hassan', 'father_name' => 'Mahmood', 'gender' => 'male', 'description' => 'Routine checkup'],
            ['name' => 'Zahra', 'father_name' => 'Abdullah', 'gender' => 'female', 'description' => 'Follow-up for diabetes'],
            ['name' => 'Karim', 'father_name' => 'Najib', 'gender' => 'male', 'description' => 'Chest pain evaluation'],
            ['name' => 'Maryam', 'father_name' => 'Yousuf', 'gender' => 'female', 'description' => 'Prenatal screening'],
            ['name' => 'Bilal', 'father_name' => 'Hamid', 'gender' => 'male', 'description' => 'Fatigue and weakness'],
            ['name' => 'Amina', 'father_name' => 'Sami', 'gender' => 'female', 'description' => 'Thyroid monitoring'],
            ['name' => 'Farid', 'father_name' => 'Rahim', 'gender' => 'male', 'description' => null],
            ['name' => 'Nadia', 'father_name' => 'Jamal', 'gender' => 'female', 'description' => 'Vitamin deficiency'],
        ];

        foreach ($patients as $patient) {
            Patient::query()->create([
                ...$patient,
                'doctor_id' => $doctors->random(),
            ]);
        }
    }
}
