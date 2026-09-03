<?php

namespace App\Observers;

use App\Models\InventoryItem;
use App\Support\LabAlerts;
use WeakMap;

class InventoryItemObserver
{
    /** @var WeakMap<InventoryItem, bool>|null */
    private static ?WeakMap $wasAtMinimum = null;

    public function creating(InventoryItem $inventoryItem): void
    {
        self::state()[$inventoryItem] = false;
    }

    public function updating(InventoryItem $inventoryItem): void
    {
        self::state()[$inventoryItem] = InventoryItem::quantityHasReachedMinimum(
            (float) $inventoryItem->getOriginal('quantity'),
            (float) $inventoryItem->getOriginal('min_quantity'),
        );
    }

    public function saved(InventoryItem $inventoryItem): void
    {
        LabAlerts::stockReachedMinimum(
            $inventoryItem,
            self::state()[$inventoryItem] ?? false,
        );

        LabAlerts::expiryFor($inventoryItem);
    }

    /**
     * @return WeakMap<InventoryItem, bool>
     */
    private static function state(): WeakMap
    {
        return self::$wasAtMinimum ??= new WeakMap;
    }
}
