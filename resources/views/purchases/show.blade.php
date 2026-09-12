<x-layout :title="$purchase->bill_number">
    <x-page-header :description="$purchase->supplier->name">
        <x-slot:actions>
            @can('records.edit')
                <x-btn :href="route('purchases.edit', $purchase)" variant="secondary" icon="edit">{{ __('Edit') }}</x-btn>
            @endcan
            <x-btn :href="route('purchases.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </x-slot:actions>
    </x-page-header>
    <dl class="lab-card mb-6 grid gap-4 p-6 sm:grid-cols-4">
        <div><dt class="text-sm text-slate-500">{{ __('Previous balance') }}</dt><dd>{{ number_format((float) $purchase->previous_balance, 2) }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Subtotal') }}</dt><dd>{{ number_format((float) $purchase->subtotal, 2) }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Received') }}</dt><dd>{{ number_format((float) $purchase->received, 2) }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Remaining') }}</dt><dd class="font-semibold">{{ number_format((float) $purchase->remaining, 2) }}</dd></div>
    </dl>
    <x-panel :title="__('Items')">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800"><tr>
                <th class="px-5 py-3 text-start">{{ __('Item') }}</th>
                <th class="px-5 py-3 text-start">{{ __('Batch') }}</th>
                <th class="px-5 py-3 text-start">{{ __('Expiry') }}</th>
                <th class="px-5 py-3 text-start">{{ __('Qty') }}</th>
                <th class="px-5 py-3 text-start">{{ __('Total') }}</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($purchase->items as $item)
                    <tr>
                        <td class="px-5 py-3">{{ $item->name }}</td>
                        <td class="px-5 py-3">{{ $item->batch_number ?: '—' }}</td>
                        <td class="px-5 py-3">{{ $item->expires_on?->toDateString() ?: '—' }}</td>
                        <td class="px-5 py-3">{{ $item->quantity }}</td>
                        <td class="px-5 py-3">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-panel>
</x-layout>
