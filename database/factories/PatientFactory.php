<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        return [
            'name' => $gender === 'male' ? fake()->firstNameMale() : fake()->firstNameFemale(),
            'father_name' => fake()->firstNameMale(),
            'gender' => $gender,
            'description' => fake()->optional()->sentence(),
            'doctor_id' => Doctor::factory(),
        ];
    }
}
