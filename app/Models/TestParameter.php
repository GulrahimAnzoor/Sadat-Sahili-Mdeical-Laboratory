<?php

namespace App\Models;

use Database\Factories\TestParameterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['test_id', 'name', 'unit', 'normal_range', 'group_name', 'sort_order', 'interpretation'])]
class TestParameter extends Model
{
    /** @use HasFactory<TestParameterFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function resultValues(): HasMany
    {
        return $this->hasMany(TestResultValue::class);
    }
}
