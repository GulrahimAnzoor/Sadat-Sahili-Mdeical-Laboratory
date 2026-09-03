<?php

namespace App\Models;

use Database\Factories\TestResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'visit_id',
    'patient_id',
    'test_id',
    'result',
    'unit',
    'organism',
    'colony_count',
    'gram_stain',
    'specimen',
    'culture_method',
])]
class TestResult extends Model
{
    /** @use HasFactory<TestResultFactory> */
    use HasFactory;

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(TestResultValue::class);
    }

    public function sensitivities(): HasMany
    {
        return $this->hasMany(CultureSensitivity::class)->orderBy('sort_order')->orderBy('id');
    }

    public function valueFor(int $parameterId): ?string
    {
        return $this->values->firstWhere('test_parameter_id', $parameterId)?->value;
    }

    public function isAbnormal(): bool
    {
        $range = $this->test?->normal_range;

        if ($range === null || ! preg_match('/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)/', $range, $matches)) {
            return false;
        }

        if (! is_numeric($this->result)) {
            return false;
        }

        $value = (float) $this->result;

        return $value < (float) $matches[1] || $value > (float) $matches[2];
    }
}
