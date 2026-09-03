<?php

namespace App\Models;

use Database\Factories\TestResultValueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['test_result_id', 'test_parameter_id', 'value'])]
class TestResultValue extends Model
{
    /** @use HasFactory<TestResultValueFactory> */
    use HasFactory;

    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class);
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(TestParameter::class, 'test_parameter_id');
    }

    public function isAbnormal(): bool
    {
        $range = $this->parameter?->normal_range;

        if ($range === null || ! preg_match('/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)/', $range, $matches)) {
            return false;
        }

        if (! is_numeric($this->value)) {
            return false;
        }

        $value = (float) $this->value;

        return $value < (float) $matches[1] || $value > (float) $matches[2];
    }
}
