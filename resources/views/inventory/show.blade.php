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

    <x-panel :title="__('Stock movements')" class="mt-6">
        @if ($item->movements->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No movements yet.') }}</p>
        @else
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                    <tr>
                        <th class="px-5 py-3 text-start">{{ __('Date') }}</th>
                        <th class="px-5 py-3 text-start">{{ __('Type') }}</th>
                        <th class="px-5 py-3 text-start">{{ __('Qty') }}</th>
                        <th class="px-5 py-3 text-start">{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($item->movements as $movement)
                        <tr>
                            <td class="px-5 py-3">{{ $movement->occurred_on->toDateString() }}</td>
                            <td class="px-5 py-3">{{ $movement->type->label() }}</td>
                            <td class="px-5 py-3 {{ $movement->type->isInbound() ? 'text-teal-700' : 'text-rose-700' }}">
                                {{ $movement->type->isInbound() ? '+' : '−' }}{{ $movement->quantity }}
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $movement->notes ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-panel>
</x-layout>
