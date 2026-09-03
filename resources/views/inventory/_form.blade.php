@php
    $item = $item ?? null;
@endphp
<div>
    <label class="mb-1 block text-sm font-medium">{{ __('Name') }}</label>
    <input name="name" required class="lab-input" value="{{ old('name', $item->name ?? '') }}">
    @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
<div>
    <label class="mb-1 block text-sm font-medium">{{ __('Category') }}</label>
    <input name="category" class="lab-input" value="{{ old('category', $item->category ?? '') }}">
    @error('category')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Quantity') }}</label>
        <input name="quantity" type="number" step="0.01" required class="lab-input" value="{{ old('quantity', $item->quantity ?? 0) }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Minimum') }}</label>
        <input name="min_quantity" type="number" step="0.01" required class="lab-input" value="{{ old('min_quantity', $item->min_quantity ?? 0) }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Unit cost') }}</label>
        <input name="unit_cost" type="number" step="0.01" required class="lab-input" value="{{ old('unit_cost', $item->unit_cost ?? 0) }}">
    </div>
</div>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Batch') }}</label>
        <input name="batch_number" class="lab-input" value="{{ old('batch_number', $item->batch_number ?? '') }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Expiry') }}</label>
        <input name="expires_on" type="date" class="lab-input" value="{{ old('expires_on', isset($item) && $item->expires_on ? $item->expires_on->toDateString() : '') }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Date') }}</label>
        <input name="received_on" type="date" class="lab-input" value="{{ old('received_on', isset($item) && $item->received_on ? $item->received_on->toDateString() : '') }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Supplier') }}</label>
        <select name="supplier_id" class="lab-input">
            <option value="">{{ __('Select') }}</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $item?->supplier_id ?? '') === (string) $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
    </div>
</div>
