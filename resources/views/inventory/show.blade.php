<x-layout :title="$item->name">
    <x-page-header>
        <x-slot:actions>
            <x-btn :href="route('inventory-items.edit', $item)" variant="secondary" icon="edit">{{ __('Edit') }}</x-btn>
            <x-btn :href="route('inventory-items.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </x-slot:actions>
    </x-page-header>
    <dl class="lab-card grid gap-4 p-6 sm:grid-cols-3">
        <div><dt class="text-sm text-slate-500">{{ __('Category') }}</dt><dd>{{ $item->category ?: '—' }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Name') }}</dt><dd class="font-semibold">{{ $item->name }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Quantity') }}</dt><dd class="font-semibold">{{ $item->quantity }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Minimum') }}</dt><dd>{{ $item->min_quantity }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Unit cost') }}</dt><dd>{{ number_format((float) $item->unit_cost, 2) }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Batch') }}</dt><dd>{{ $item->batch_number ?: '—' }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Expiry') }}</dt><dd>{{ $item->expires_on?->toDateString() ?: '—' }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Date') }}</dt><dd>{{ $item->received_on?->toDateString() ?: '—' }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Supplier') }}</dt><dd>{{ $item->supplier?->name ?: '—' }}</dd></div>
    </dl>
</x-layout>
