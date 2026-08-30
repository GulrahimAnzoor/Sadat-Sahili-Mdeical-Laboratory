<?php

namespace App\Models;

use Database\Factories\PatientTestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['patient_id', 'test_id', 'total_price', 'paid'])]
class PatientTest extends Model
{
    /** @use HasFactory<PatientTestFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'paid' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
