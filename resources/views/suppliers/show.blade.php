<x-layout :title="$supplier->name">
    <x-page-header>
        <x-slot:actions>
            <x-btn :href="route('purchases.create', ['supplier_id' => $supplier->id])" icon="plus">{{ __('New purchase') }}</x-btn>
            @can('records.edit')
                <x-btn :href="route('suppliers.edit', $supplier)" variant="secondary" icon="edit">{{ __('Edit') }}</x-btn>
            @endcan
        </x-slot:actions>
    </x-page-header>
    <dl class="lab-card mb-6 grid gap-4 p-6 sm:grid-cols-3">
        <div><dt class="text-sm text-slate-500">{{ __('Phone') }}</dt><dd>{{ $supplier->phone ?: '—' }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Address') }}</dt><dd>{{ $supplier->address ?: '—' }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Balance') }}</dt><dd class="font-semibold">{{ number_format((float) $supplier->current_balance, 2) }}</dd></div>
    </dl>
    <x-panel :title="__('Purchases')">
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($supplier->purchases as $purchase)
                <li class="flex justify-between px-5 py-3 text-sm">
                    <a href="{{ route('purchases.show', $purchase) }}" class="font-medium">{{ $purchase->bill_number }}</a>
                    <span>{{ number_format((float) $purchase->remaining, 2) }}</span>
                </li>
            @empty
                <li class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No purchases yet.') }}</li>
            @endforelse
        </ul>
    </x-panel>
</x-layout>
