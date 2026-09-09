<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'inventory_item_id',
    'type',
    'quantity',
    'occurred_on',
    'visit_id',
    'patient_test_id',
    'purchase_id',
    'user_id',
    'notes',
])]
class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'quantity' => 'decimal:2',
            'occurred_on' => 'date',
        ];
    }

    #[Scope]
    protected function inbound(Builder $query): Builder
    {
        return $query->whereIn('type', StockMovementType::inbound());
    }

    #[Scope]
    protected function outbound(Builder $query): Builder
    {
        return $query->whereIn('type', StockMovementType::outbound());
    }

    #[Scope]
    protected function occurredBetween(Builder $query, ?Carbon $from, ?Carbon $to): Builder
    {
        return $query
            ->when($from, fn (Builder $scoped): Builder => $scoped->whereDate('occurred_on', '>=', $from))
            ->when($to, fn (Builder $scoped): Builder => $scoped->whereDate('occurred_on', '<=', $to));
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patientTest(): BelongsTo
    {
        return $this->belongsTo(PatientTest::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
