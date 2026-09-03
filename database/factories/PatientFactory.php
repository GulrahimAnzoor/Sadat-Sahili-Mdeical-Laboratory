<?php

namespace Database\Factories;

use App\Enums\AgeUnit;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        return [
            'name' => $gender === 'male' ? fake()->firstNameMale() : fake()->firstNameFemale(),
            'father_name' => fake()->firstNameMale(),
            'gender' => $gender,
            'age' => fake()->numberBetween(1, 80),
            'age_unit' => AgeUnit::Years,
            'phone' => fake()->optional()->numerify('07########'),
            'description' => fake()->optional()->sentence(),
            'doctor_id' => Doctor::factory(),
        ];
    }

    public function selfRequest(): static
    {
        return $this->state(fn (): array => [
            'doctor_id' => null,
        ]);
    }

    public function monthsOld(int $months = 8): static
    {
        return $this->state(fn (): array => [
            'age' => $months,
            'age_unit' => AgeUnit::Months,
        ]);
    }
}
