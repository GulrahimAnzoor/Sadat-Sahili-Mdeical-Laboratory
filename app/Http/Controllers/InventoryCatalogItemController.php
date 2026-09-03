<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryCatalogItemRequest;
use App\Models\InventoryCatalogItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryCatalogItemController extends Controller
{
    public function index(): View
    {
        return view('settings.goods', [
            'items' => InventoryCatalogItem::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(StoreInventoryCatalogItemRequest $request): RedirectResponse
    {
        InventoryCatalogItem::query()->create([
            'name' => $request->validated('name'),
        ]);

        return redirect()
            ->route('settings.goods.index')
            ->with('success', __('Item name saved.'));
    }

    public function destroy(InventoryCatalogItem $inventoryCatalogItem): RedirectResponse
    {
        $inventoryCatalogItem->delete();

        return redirect()
            ->route('settings.goods.index')
            ->with('success', __('Item name deleted.'));
    }
}
