<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockUsageRequest;
use App\Models\InventoryItem;
use App\Services\StockLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockUsageController extends Controller
{
    public function create(): View
    {
        $lots = InventoryItem::query()
            ->inStock()
            ->orderBy('name')
            ->orderBy('expires_on')
            ->orderBy('id')
            ->get(['id', 'name', 'quantity', 'batch_number', 'expires_on']);

        $rows = collect(old('items', []))->map(function (mixed $row) use ($lots): array {
            $row = is_array($row) ? $row : [];
            $lot = $lots->firstWhere('id', (int) ($row['inventory_item_id'] ?? 0));

            return [
                'inventory_item_id' => $row['inventory_item_id'] ?? '',
                'quantity' => $row['quantity'] ?? '',
                'item' => $lot,
            ];
        });

        return view('stock-usages.create', [
            'lots' => $lots,
            'rows' => $rows,
        ]);
    }

    public function store(StoreStockUsageRequest $request, StockLedger $stock): RedirectResponse
    {
        DB::transaction(function () use ($request, $stock): void {
            $stock->recordUsage(
                $request->validated('items'),
                $request->string('used_on')->toString(),
                $request->validated('notes'),
                $request->user()?->id,
            );
        });

        return redirect()
            ->route('inventory-items.index')
            ->with('success', __('Lab usage recorded. Stock updated.'));
    }
}
