<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            'Dr. Ahmad Sadat',
            'Dr. Fatima Sahili',
            'Dr. Omar Karimi',
            'Dr. Layla Ahmadi',
        ] as $name) {
            Doctor::query()->create(['name' => $name]);
        }
    }
}
