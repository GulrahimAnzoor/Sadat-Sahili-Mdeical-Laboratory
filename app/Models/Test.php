<?php

namespace App\Models;

use Database\Factories\TestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'price', 'normal_range'])]
class Test extends Model
{
    /** @use HasFactory<TestFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'patient_tests')
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
