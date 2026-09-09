<?php

namespace App\Models;

use App\Enums\ExpiryAlertStage;
use App\Observers\InventoryItemObserver;
use Database\Factories\InventoryItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([InventoryItemObserver::class])]
#[Fillable([
    'name',
    'category',
    'quantity',
    'min_quantity',
    'unit_cost',
    'batch_number',
    'expires_on',
    'supplier_id',
    'received_on',
])]
class InventoryItem extends Model
{
    /** @use HasFactory<InventoryItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'min_quantity' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'expires_on' => 'date',
            'received_on' => 'date',
            'expiry_alert_stage' => ExpiryAlertStage::class,
        ];
    }

    public function isLow(): bool
    {
        return (float) $this->quantity <= (float) $this->min_quantity;
    }

    public function hasReachedMinimum(): bool
    {
        return self::quantityHasReachedMinimum((float) $this->quantity, (float) $this->min_quantity);
    }

    public static function quantityHasReachedMinimum(float $quantity, float $minimum): bool
    {
        return $minimum > 0 && $quantity <= $minimum;
    }

    public function isExpiringSoon(): bool
    {
        return $this->expires_on !== null && $this->expires_on->lte(now()->addDays(30));
    }

    public function dueExpiryStage(): ?ExpiryAlertStage
    {
        if ($this->expires_on === null) {
            return null;
        }

        $days = (int) now()->startOfDay()->diffInDays($this->expires_on->copy()->startOfDay(), false);

        if ($days <= 0) {
            return ExpiryAlertStage::Expired;
        }

        if ($days <= 10) {
            return ExpiryAlertStage::TenDays;
        }

        if ($days <= 30) {
            return ExpiryAlertStage::Month;
        }

        return null;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    #[Scope]
    protected function inStock(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }

    public function lotLabel(): string
    {
        $parts = [
            $this->name,
            $this->batch_number ? __('Batch').' '.$this->batch_number : null,
            $this->expires_on?->toDateString(),
            $this->quantity.' '.__('on hand'),
        ];

        return collect($parts)->filter()->implode(' · ');
    }
}
