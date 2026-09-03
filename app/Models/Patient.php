<?php

namespace App\Models;

use App\Enums\AgeUnit;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'file_number',
    'name',
    'father_name',
    'gender',
    'age',
    'age_unit',
    'phone',
    'address',
    'description',
    'doctor_id',
])]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'age_unit' => 'y',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'age_unit' => AgeUnit::class,
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Patient $patient): void {
            if ($patient->file_number !== null) {
                return;
            }

            $patient->updateQuietly([
                'file_number' => 'P-'.str_pad((string) $patient->id, 5, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function genderLabel(): string
    {
        return match ($this->gender) {
            'male' => __('Male'),
            'female' => __('Female'),
            default => $this->gender,
        };
    }

    public function ageLabel(): string
    {
        if ($this->age === null) {
            return '';
        }

        $unit = $this->age_unit ?? AgeUnit::Years;

        return $this->age.' '.$unit->value;
    }

    public function genderAndAgeLabel(): string
    {
        $age = $this->ageLabel();

        return $age === ''
            ? $this->genderLabel()
            : $this->genderLabel().' / '.$age;
    }

    public function referrerLabel(): string
    {
        return $this->doctor?->name ?? __('Self request');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function tests(): BelongsToMany
    {
        return $this->belongsToMany(Test::class, 'patient_tests')
            ->withPivot(['total_price', 'paid'])
            ->withTimestamps();
    }

    public function patientTests(): HasMany
    {
        return $this->hasMany(PatientTest::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }
}
