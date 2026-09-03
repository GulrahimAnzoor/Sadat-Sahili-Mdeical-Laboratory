<?php

namespace App\Models;

use App\Enums\Sensitivity;
use Database\Factories\CultureSensitivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['test_result_id', 'antibiotic', 'sensitivity', 'sort_order'])]
class CultureSensitivity extends Model
{
    /** @use HasFactory<CultureSensitivityFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sensitivity' => Sensitivity::class,
            'sort_order' => 'integer',
        ];
    }

    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class);
    }
}
