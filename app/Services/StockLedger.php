<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\InventoryItem;
use App\Models\PatientTest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class StockLedger
{
    /**
     * @param  array{visit_id?: int|null, patient_test_id?: int|null, purchase_id?: int|null, notes?: string|null, user_id?: int|null, occurred_on?: Carbon|string|null}  $context
     */
    public function receive(InventoryItem $item, float $quantity, StockMovementType $type, array $context = []): StockMovement
    {
        $this->assertPositiveQuantity($quantity);

        $lot = $this->lockedLot($item->id);
        $lot->quantity = round((float) $lot->quantity + $quantity, 2);
        $lot->save();

        return $this->record($lot, $type, $quantity, $context);
    }

    /**
     * @param  array{visit_id?: int|null, patient_test_id?: int|null, purchase_id?: int|null, notes?: string|null, user_id?: int|null, occurred_on?: Carbon|string|null}  $context
     */
    public function consumeFromLot(InventoryItem $item, float $quantity, StockMovementType $type, array $context = []): StockMovement
    {
        $this->assertPositiveQuantity($quantity);

        $lot = $this->lockedLot($item->id);
        $available = (float) $lot->quantity;

        if ($available + 0.0001 < $quantity) {
            $this->throwInsufficientStock($lot->name, $available);
        }

        $lot->quantity = round($available - $quantity, 2);
        $lot->save();

        return $this->record($lot, $type, $quantity, $context);
    }

    /**
     * @param  array{visit_id?: int|null, patient_test_id?: int|null, purchase_id?: int|null, notes?: string|null, user_id?: int|null, occurred_on?: Carbon|string|null}  $context
     */
    public function consumeByName(string $name, float $quantity, StockMovementType $type, array $context = []): void
    {
        $this->assertPositiveQuantity($quantity);

        $lots = InventoryItem::query()
            ->where('name', $name)
            ->where('quantity', '>', 0)
            ->orderByRaw('case when expires_on is null then 1 else 0 end')
            ->orderBy('expires_on')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $available = (float) $lots->sum(fn (InventoryItem $lot): float => (float) $lot->quantity);

        if ($available + 0.0001 < $quantity) {
            $this->throwInsufficientStock($name, $available);
        }

        $remaining = $quantity;

        foreach ($lots as $lot) {
            if ($remaining <= 0) {
                break;
            }

            $take = min((float) $lot->quantity, $remaining);
            $lot->quantity = round((float) $lot->quantity - $take, 2);
            $lot->save();
            $this->record($lot, $type, $take, $context);
            $remaining = round($remaining - $take, 2);
        }
    }

    /**
     * @param  list<array{inventory_item_id?: int|string|null, quantity?: float|int|string|null}>  $lines
     */
    public function replaceTestConsumption(PatientTest $patientTest, Visit $visit, array $lines, ?int $userId = null): void
    {
        $existing = StockMovement::query()
            ->where('patient_test_id', $patientTest->id)
            ->where('type', StockMovementType::OutTest)
            ->get();

        foreach ($existing as $movement) {
            $lot = $this->lockedLot($movement->inventory_item_id);
            $lot->quantity = round((float) $lot->quantity + (float) $movement->quantity, 2);
            $lot->save();
            $movement->delete();
        }

        foreach ($this->normalizedLines($lines) as $line) {
            $this->consumeFromLot(
                InventoryItem::query()->findOrFail($line['inventory_item_id']),
                $line['quantity'],
                StockMovementType::OutTest,
                [
                    'visit_id' => $visit->id,
                    'patient_test_id' => $patientTest->id,
                    'user_id' => $userId,
                    'occurred_on' => now()->toDateString(),
                ],
            );
        }
    }

    /**
     * @param  list<array{inventory_item_id?: int|string|null, quantity?: float|int|string|null}>  $lines
     */
    public function recordUsage(array $lines, string $usedOn, ?string $notes, ?int $userId = null): void
    {
        foreach ($this->normalizedLines($lines) as $line) {
            $this->consumeFromLot(
                InventoryItem::query()->findOrFail($line['inventory_item_id']),
                $line['quantity'],
                StockMovementType::OutUsage,
                [
                    'notes' => $notes,
                    'user_id' => $userId,
                    'occurred_on' => $usedOn,
                ],
            );
        }
    }

    public function receivePurchaseItem(Purchase $purchase, PurchaseItem $item, int $supplierId): InventoryItem
    {
        $lot = $this->findOrNewLot(
            $item->name,
            $item->batch_number,
            $item->expires_on?->toDateString(),
        );

        $lot->name = $item->name;
        $lot->unit_cost = $item->unit_price;
        $lot->batch_number = $this->normalizeBatch($item->batch_number);
        $lot->expires_on = $item->expires_on;
        $lot->supplier_id = $supplierId;

        if (! $lot->exists) {
            $lot->quantity = 0;
            $lot->min_quantity = $lot->min_quantity ?? 0;
            $lot->received_on = $purchase->billed_on;
        }

        $lot->save();

        $this->receive($lot, (float) $item->quantity, StockMovementType::InPurchase, [
            'purchase_id' => $purchase->id,
            'user_id' => auth()->id(),
            'occurred_on' => $purchase->billed_on?->toDateString() ?? now()->toDateString(),
        ]);

        return $lot->refresh();
    }

    public function revertPurchase(Purchase $purchase): void
    {
        $movements = StockMovement::query()
            ->where('purchase_id', $purchase->id)
            ->where('type', StockMovementType::InPurchase)
            ->get();

        foreach ($movements as $movement) {
            $lot = $this->lockedLot($movement->inventory_item_id);
            $lot->quantity = max(0, round((float) $lot->quantity - (float) $movement->quantity, 2));
            $lot->save();
            $movement->delete();
        }
    }

    public function recordManualReceipt(InventoryItem $item): StockMovement
    {
        return $this->record($item, StockMovementType::InManual, (float) $item->quantity, [
            'user_id' => auth()->id(),
            'occurred_on' => $item->received_on?->toDateString() ?? now()->toDateString(),
        ]);
    }

    public function backfillOpeningReceipts(): int
    {
        $count = 0;

        InventoryItem::query()->orderBy('id')->each(function (InventoryItem $item) use (&$count): void {
            $inbound = (float) $item->movements()->inbound()->sum('quantity');
            $outbound = (float) $item->movements()->outbound()->sum('quantity');
            $gap = round((float) $item->quantity + $outbound - $inbound, 2);

            if ($gap <= 0) {
                return;
            }

            $this->record($item, StockMovementType::InManual, $gap, [
                'occurred_on' => $item->received_on?->toDateString()
                    ?? $item->created_at?->toDateString()
                    ?? now()->toDateString(),
                'notes' => 'Opening stock',
            ]);
            $count++;
        });

        return $count;
    }

    public function findOrNewLot(string $name, ?string $batchNumber, ?string $expiresOn): InventoryItem
    {
        $batchNumber = $this->normalizeBatch($batchNumber);
        $query = InventoryItem::query()->where('name', $name);

        if ($batchNumber === null) {
            $query->where(function ($scoped): void {
                $scoped->whereNull('batch_number')->orWhere('batch_number', '');
            });
        } else {
            $query->where('batch_number', $batchNumber);
        }

        if ($expiresOn === null || $expiresOn === '') {
            $query->whereNull('expires_on');
        } else {
            $query->whereDate('expires_on', $expiresOn);
        }

        return $query->lockForUpdate()->first() ?? new InventoryItem(['name' => $name, 'quantity' => 0]);
    }

    /**
     * @param  array{visit_id?: int|null, patient_test_id?: int|null, purchase_id?: int|null, notes?: string|null, user_id?: int|null, occurred_on?: Carbon|string|null}  $context
     */
    private function record(InventoryItem $item, StockMovementType $type, float $quantity, array $context): StockMovement
    {
        $occurredOn = $context['occurred_on'] ?? now()->toDateString();

        if ($occurredOn instanceof Carbon) {
            $occurredOn = $occurredOn->toDateString();
        }

        return StockMovement::query()->create([
            'inventory_item_id' => $item->id,
            'type' => $type,
            'quantity' => $quantity,
            'occurred_on' => $occurredOn,
            'visit_id' => $context['visit_id'] ?? null,
            'patient_test_id' => $context['patient_test_id'] ?? null,
            'purchase_id' => $context['purchase_id'] ?? null,
            'user_id' => $context['user_id'] ?? auth()->id(),
            'notes' => $context['notes'] ?? null,
        ]);
    }

    private function lockedLot(int $id): InventoryItem
    {
        return InventoryItem::query()->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    private function assertPositiveQuantity(float $quantity): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => __('Quantity must be greater than zero.'),
            ]);
        }
    }

    private function throwInsufficientStock(string $name, float $available): never
    {
        throw ValidationException::withMessages([
            'quantity' => __('Not enough stock for :item. Available: :available.', [
                'item' => $name,
                'available' => number_format($available, 2, '.', ''),
            ]),
        ]);
    }

    /**
     * @param  list<array{inventory_item_id?: int|string|null, quantity?: float|int|string|null}>  $lines
     * @return list<array{inventory_item_id: int, quantity: float}>
     */
    private function normalizedLines(array $lines): array
    {
        $normalized = [];

        foreach ($lines as $line) {
            $id = (int) ($line['inventory_item_id'] ?? 0);
            $quantity = (float) ($line['quantity'] ?? 0);

            if ($id < 1 || $quantity <= 0) {
                continue;
            }

            $normalized[] = [
                'inventory_item_id' => $id,
                'quantity' => $quantity,
            ];
        }

        return $normalized;
    }

    private function normalizeBatch(?string $batchNumber): ?string
    {
        $batchNumber = trim((string) $batchNumber);

        return $batchNumber === '' ? null : $batchNumber;
    }
}
