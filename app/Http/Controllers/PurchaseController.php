<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\StockLedger;
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

    public function store(StorePurchaseRequest $request, CashLedger $ledger, StockLedger $stock): RedirectResponse
    {
        $purchase = DB::transaction(function () use ($request, $ledger, $stock): Purchase {
            $purchase = $this->persist($request, $stock);
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

    public function update(UpdatePurchaseRequest $request, Purchase $purchase, CashLedger $ledger, StockLedger $stock): RedirectResponse
    {
        DB::transaction(function () use ($request, $purchase, $ledger, $stock): void {
            $stock->revertPurchase($purchase);
            $purchase->items()->delete();
            $this->persist($request, $stock, $purchase);
            $ledger->syncPurchase($purchase->refresh());
        });

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', __('Purchase updated successfully.'));
    }

    public function destroy(Purchase $purchase, CashLedger $ledger, StockLedger $stock): RedirectResponse
    {
        DB::transaction(function () use ($purchase, $ledger, $stock): void {
            $stock->revertPurchase($purchase);
            $ledger->forgetPurchase($purchase);
            $purchase->delete();
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', __('Purchase deleted.'));
    }

    private function persist(StorePurchaseRequest $request, StockLedger $stock, ?Purchase $purchase = null): Purchase
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

            $purchaseItem = $purchase->items()->create([
                'name' => $item['name'],
                'generic_name' => $item['generic_name'] ?? null,
                'manufacturer' => $item['manufacturer'] ?? null,
                'batch_number' => $item['batch_number'] ?? null,
                'expires_on' => $item['expires_on'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $lineTotal,
            ]);

            $stock->receivePurchaseItem($purchase, $purchaseItem, $supplier->id);
        }

        $supplier->update(['current_balance' => $remaining]);

        return $purchase->refresh();
    }
}
