<?php

namespace App\Models;

use App\Casts\AsDepartment;
use Database\Factories\TestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
    'department',
    'price',
    'normal_range',
    'is_active',
    'interpretation',
    'clinical_utility',
    'method',
])]
class Test extends Model
{
    /** @use HasFactory<TestFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'department' => 'routine',
        'is_active' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'department' => AsDepartment::class,
        ];
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
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

    public function parameters(): HasMany
    {
        return $this->hasMany(TestParameter::class)->orderBy('sort_order')->orderBy('id');
    }
}
