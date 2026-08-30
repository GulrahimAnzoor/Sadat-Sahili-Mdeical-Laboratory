<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'father_name', 'gender', 'description', 'doctor_id'])]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

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

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }
}
