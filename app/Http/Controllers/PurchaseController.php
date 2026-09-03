<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Support\CashLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View
    {
        return view('purchases.index', [
            'purchases' => Purchase::query()
                ->with('supplier')
                ->latest('billed_on')
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('purchases.create', [
            'suppliers' => Supplier::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request, CashLedger $ledger): RedirectResponse
    {
        $purchase = DB::transaction(function () use ($request, $ledger): Purchase {
            $purchase = $this->persist($request);
            $ledger->syncPurchase($purchase);

            return $purchase;
        });

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', __('Purchase saved successfully.'));
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'items']);

        return view('purchases.show', ['purchase' => $purchase]);
    }

    public function edit(Purchase $purchase): View
    {
        $purchase->load('items');

        return view('purchases.edit', [
            'purchase' => $purchase,
            'suppliers' => Supplier::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase, CashLedger $ledger): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchase, $ledger): void {
            $this->revertStock($purchase);
            $purchase->items()->delete();
            $this->persist($request, $purchase);
            $ledger->syncPurchase($purchase->refresh());
        });

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', __('Purchase updated successfully.'));
    }

    public function destroy(Purchase $purchase, CashLedger $ledger): RedirectResponse
    {
        DB::transaction(function () use ($purchase, $ledger): void {
            $this->revertStock($purchase);
            $ledger->forgetPurchase($purchase);
            $purchase->delete();
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', __('Purchase deleted.'));
    }

    private function persist(StorePurchaseRequest $request, ?Purchase $purchase = null): Purchase
    {
        $validated = $request->validated();
        $supplier = Supplier::query()->findOrFail($validated['supplier_id']);
        $subtotal = 0.0;

        foreach ($validated['items'] as $item) {
            $subtotal += (float) $item['quantity'] * (float) $item['unit_price'];
        }

        $previous = $purchase === null
            ? (float) $supplier->current_balance
            : (float) $purchase->previous_balance;
        $received = (float) $validated['received'];
        $remaining = $previous + $subtotal - $received;

        $values = [
            'supplier_id' => $supplier->id,
            'bill_number' => $validated['bill_number'],
            'billed_on' => $validated['billed_on'],
            'type' => $validated['type'],
            'previous_balance' => $previous,
            'subtotal' => $subtotal,
            'received' => $received,
            'remaining' => $remaining,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($purchase === null) {
            $purchase = Purchase::query()->create($values);
        } else {
            $purchase->update($values);
        }

        foreach ($validated['items'] as $item) {
            $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];

            $purchase->items()->create([
                'name' => $item['name'],
                'generic_name' => $item['generic_name'] ?? null,
                'manufacturer' => $item['manufacturer'] ?? null,
                'batch_number' => $item['batch_number'] ?? null,
                'expires_on' => $item['expires_on'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
            ]);

            $inventory = InventoryItem::query()->firstOrNew(['name' => $item['name']]);
            $inventory->quantity = (float) $inventory->quantity + (float) $item['quantity'];
            $inventory->unit_cost = $item['unit_price'];
            $inventory->batch_number = $item['batch_number'] ?? $inventory->batch_number;
            $inventory->expires_on = $item['expires_on'] ?? $inventory->expires_on;
            $inventory->supplier_id = $supplier->id;
            $inventory->save();
        }

        $supplier->update(['current_balance' => $remaining]);

        return $purchase->refresh();
    }

    private function revertStock(Purchase $purchase): void
    {
        $purchase->loadMissing('items');

        foreach ($purchase->items as $item) {
            $inventory = InventoryItem::query()->where('name', $item->name)->first();

            if ($inventory === null) {
                continue;
            }

            $inventory->update([
                'quantity' => max(0, (float) $inventory->quantity - (float) $item->quantity),
            ]);
        }
    }
}
