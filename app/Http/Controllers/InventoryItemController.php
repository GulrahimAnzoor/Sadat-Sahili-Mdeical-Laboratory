<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Models\InventoryCatalogItem;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function index(): View
    {
        return view('inventory.index', [
            'items' => InventoryItem::query()
                ->with('supplier')
                ->orderBy('category')
                ->orderBy('name')
                ->orderByDesc('id')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('inventory.create', [
            'suppliers' => Supplier::query()->orderBy('name')->orderBy('id')->get(),
            'categories' => InventoryCatalogItem::query()
                ->orderBy('name')
                ->pluck('name')
                ->merge(InventoryItem::query()->whereNotNull('category')->orderBy('category')->pluck('category'))
                ->unique()
                ->sort()
                ->values(),
            'itemNames' => InventoryItem::query()
                ->orderBy('name')
                ->pluck('name')
                ->unique()
                ->values(),
            'lots' => $this->draftLots(),
        ]);
    }

    public function store(StoreInventoryItemRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $count = DB::transaction(function () use ($validated): int {
            InventoryCatalogItem::query()->firstOrCreate(['name' => $validated['category']]);

            foreach ($validated['lots'] as $lot) {
                InventoryItem::query()->create([
                    'name' => $lot['name'],
                    'category' => $validated['category'],
                    'quantity' => $lot['quantity'],
                    'min_quantity' => $lot['min_quantity'],
                    'unit_cost' => $lot['unit_cost'],
                    'batch_number' => $lot['batch_number'] ?? null,
                    'expires_on' => $lot['expires_on'] ?? null,
                    'supplier_id' => $validated['supplier_id'],
                    'received_on' => $validated['received_on'],
                ]);
            }

            return count($validated['lots']);
        });

        return redirect()
            ->route('inventory-items.create')
            ->with('success', __(':count items saved in :category.', [
                'count' => $count,
                'category' => $validated['category'],
            ]));
    }

    public function show(InventoryItem $inventoryItem): View
    {
        $inventoryItem->load('supplier');

        return view('inventory.show', ['item' => $inventoryItem]);
    }

    public function edit(InventoryItem $inventoryItem): View
    {
        return view('inventory.edit', [
            'item' => $inventoryItem,
            'suppliers' => Supplier::query()->orderBy('name')->orderBy('id')->get(),
        ]);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): RedirectResponse
    {
        $inventoryItem->update($request->validated());

        return redirect()
            ->route('inventory-items.show', $inventoryItem)
            ->with('success', __('Inventory item updated.'));
    }

    public function destroy(InventoryItem $inventoryItem): RedirectResponse
    {
        $inventoryItem->delete();

        return redirect()
            ->route('inventory-items.index')
            ->with('success', __('Inventory item deleted.'));
    }

    /**
     * @return list<array{name: mixed, quantity: mixed, min_quantity: mixed, unit_cost: mixed, batch_number: mixed, expires_on: mixed}>
     */
    private function draftLots(): array
    {
        $lots = collect(old('lots', []))
            ->map(fn (mixed $lot): array => [
                'name' => is_array($lot) ? ($lot['name'] ?? '') : '',
                'quantity' => is_array($lot) ? ($lot['quantity'] ?? '') : '',
                'min_quantity' => is_array($lot) ? ($lot['min_quantity'] ?? '0') : '0',
                'unit_cost' => is_array($lot) ? ($lot['unit_cost'] ?? '0') : '0',
                'batch_number' => is_array($lot) ? ($lot['batch_number'] ?? '') : '',
                'expires_on' => is_array($lot) ? ($lot['expires_on'] ?? '') : '',
            ])
            ->all();

        while (count($lots) < 1) {
            $lots[] = [
                'name' => '',
                'quantity' => '',
                'min_quantity' => '0',
                'unit_cost' => '0',
                'batch_number' => '',
                'expires_on' => '',
            ];
        }

        return $lots;
    }
}
