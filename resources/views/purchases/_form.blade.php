@php
    $purchase = $purchase ?? null;
    $items = old('items', $purchase ? $purchase->items->map->only(['name','generic_name','manufacturer','batch_number','expires_on','quantity','unit_price'])->all() : []);
    while (count($items) < 4) {
        $items[] = ['name' => '', 'generic_name' => '', 'manufacturer' => '', 'batch_number' => '', 'expires_on' => '', 'quantity' => '', 'unit_price' => ''];
    }
@endphp
<div>
    <label class="mb-1 block text-sm font-medium">{{ __('Supplier') }}</label>
    <select name="supplier_id" required class="lab-input">
        <option value="">{{ __('Select') }}</option>
        @foreach ($suppliers as $supplier)
            <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $purchase->supplier_id ?? request('supplier_id')) === (string) $supplier->id)>{{ $supplier->name }}</option>
        @endforeach
    </select>
    @error('supplier_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Bill no.') }}</label>
        <input name="bill_number" required class="lab-input" value="{{ old('bill_number', $purchase->bill_number ?? '') }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Date') }}</label>
        <input name="billed_on" type="date" required class="lab-input" value="{{ old('billed_on', $purchase?->billed_on?->toDateString() ?? now()->toDateString()) }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Bill type') }}</label>
        <select name="type" class="lab-input">
            <option value="simple" @selected(old('type', $purchase?->type->value ?? 'simple') === 'simple')>{{ __('Simple bill') }}</option>
            <option value="reagent" @selected(old('type', $purchase?->type->value ?? '') === 'reagent')>{{ __('Reagent bill') }}</option>
        </select>
    </div>
</div>
<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="text-slate-500"><tr>
            <th class="py-2 text-start">{{ __('Item') }}</th>
            <th class="py-2 text-start">{{ __('Generic') }}</th>
            <th class="py-2 text-start">{{ __('Manufacturer') }}</th>
            <th class="py-2 text-start">{{ __('Batch') }}</th>
            <th class="py-2 text-start">{{ __('Expiry') }}</th>
            <th class="py-2 text-start">{{ __('Qty') }}</th>
            <th class="py-2 text-start">{{ __('Price') }}</th>
        </tr></thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr>
                    <td class="pr-2 py-1"><input name="items[{{ $i }}][name]" class="lab-input" value="{{ $item['name'] ?? '' }}"></td>
                    <td class="pr-2 py-1"><input name="items[{{ $i }}][generic_name]" class="lab-input" value="{{ $item['generic_name'] ?? '' }}"></td>
                    <td class="pr-2 py-1"><input name="items[{{ $i }}][manufacturer]" class="lab-input" value="{{ $item['manufacturer'] ?? '' }}"></td>
                    <td class="pr-2 py-1"><input name="items[{{ $i }}][batch_number]" class="lab-input" value="{{ $item['batch_number'] ?? '' }}"></td>
                    <td class="pr-2 py-1"><input name="items[{{ $i }}][expires_on]" type="date" class="lab-input" value="{{ $item['expires_on'] ?? '' }}"></td>
                    <td class="pr-2 py-1"><input name="items[{{ $i }}][quantity]" type="number" step="0.01" class="lab-input" value="{{ $item['quantity'] ?? '' }}"></td>
                    <td class="py-1"><input name="items[{{ $i }}][unit_price]" type="number" step="0.01" class="lab-input" value="{{ $item['unit_price'] ?? '' }}"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @error('items')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
<div>
    <label class="mb-1 block text-sm font-medium">{{ __('Received') }}</label>
    <input name="received" type="number" step="0.01" min="0" required class="lab-input" value="{{ old('received', $purchase->received ?? 0) }}">
</div>
<div>
    <label class="mb-1 block text-sm font-medium">{{ __('Notes') }}</label>
    <textarea name="notes" rows="2" class="lab-input">{{ old('notes', $purchase->notes ?? '') }}</textarea>
</div>
