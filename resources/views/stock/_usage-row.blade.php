@php
    $item = $row['item'] ?? $row['inventoryItem'] ?? null;
    $id = $row['inventory_item_id'] ?? $item?->id ?? '';
    $name = $row['name'] ?? $item?->name ?? '';
    $onHand = $row['on_hand'] ?? $item?->quantity ?? '—';
    $expiry = $row['expiry'] ?? $item?->expires_on?->toDateString() ?? '—';
    $batch = $row['batch'] ?? $item?->batch_number ?? '—';
    $quantity = $row['quantity'] ?? '1';
    $formAttribute = filled($formId ?? null) ? ' form="'.$formId.'"' : '';
@endphp
<article data-stock-row class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <input type="hidden" name="{{ $namePrefix }}[{{ $index }}][inventory_item_id]" value="{{ $id }}" data-stock-id{!! $formAttribute !!}>
            <p data-stock-name class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $name }}</p>
            <p class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                <span>{{ __('Batch') }}: <span data-stock-batch>{{ $batch ?: '—' }}</span></span>
                <span>{{ __('On hand') }}: <span data-stock-onhand>{{ $onHand }}</span></span>
                <span>{{ __('Expiry') }}: <span data-stock-expiry>{{ $expiry ?: '—' }}</span></span>
            </p>
        </div>
        <button type="button" data-stock-remove class="shrink-0 rounded-lg px-2 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40">{{ __('Remove') }}</button>
    </div>
    <div class="mt-3 flex items-center gap-3">
        <label class="text-xs font-medium text-slate-500">{{ __('Qty') }}</label>
        <input
            type="number"
            step="0.01"
            min="0.01"
            name="{{ $namePrefix }}[{{ $index }}][quantity]"
            value="{{ $quantity }}"
            data-stock-qty
            class="lab-input max-w-28"
            {!! $formAttribute !!}
        >
    </div>
</article>
