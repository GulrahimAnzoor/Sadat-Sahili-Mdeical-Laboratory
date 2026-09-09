<x-layout :title="__('Inventory')">
    <x-page-header :description="__('Received, consumed, remaining stock and expiry')">
        <x-slot:actions>
            <x-settings-back />
            <x-btn :href="route('stock-usages.create')" variant="secondary" icon="flask">{{ __('Lab usage') }}</x-btn>
            <x-btn :href="route('inventory-items.create')" icon="plus">{{ __('New item') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('inventory-items.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs text-slate-500">{{ __('From') }}</label>
            <input type="date" name="from" value="{{ $from }}" class="lab-input">
        </div>
        <div>
            <label class="mb-1 block text-xs text-slate-500">{{ __('To') }}</label>
            <input type="date" name="to" value="{{ $to }}" class="lab-input">
        </div>
        <x-btn type="submit" variant="secondary" size="sm">{{ __('Filter') }}</x-btn>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="lab-card p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Received') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($received, 2) }}</p>
        </div>
        <div class="lab-card p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Consumed') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($consumed, 2) }}</p>
        </div>
        <div class="lab-card p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('On hand') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($onHand, 2) }}</p>
        </div>
        <div class="lab-card p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Expiring / expired') }}</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white">{{ $expiring }}</p>
        </div>
    </div>

    <x-panel>
        @if ($items->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('Inventory is empty.') }}</p>
        @else
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800"><tr>
                    <th class="px-5 py-3 text-start">{{ __('Category') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Item') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Received') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Consumed') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('On hand') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Batch') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Expiry') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Status') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Actions') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($items as $item)
                        <tr>
                            <td class="px-5 py-3 text-slate-500">{{ $item->category ?: '—' }}</td>
                            <td class="px-5 py-3"><a class="font-medium text-teal-800 dark:text-teal-300" href="{{ route('inventory-items.show', $item) }}">{{ $item->name }}</a></td>
                            <td class="px-5 py-3">{{ number_format((float) ($item->received_qty ?? 0), 2) }}</td>
                            <td class="px-5 py-3">{{ number_format((float) ($item->consumed_qty ?? 0), 2) }}</td>
                            <td class="px-5 py-3">{{ $item->quantity }}</td>
                            <td class="px-5 py-3">{{ $item->batch_number ?: '—' }}</td>
                            <td class="px-5 py-3">{{ $item->expires_on?->toDateString() ?: '—' }}</td>
                            <td class="px-5 py-3">
                                @if ($item->isLow())
                                    <x-badge tone="rose">{{ __('Low stock') }}</x-badge>
                                @elseif ($item->expires_on !== null && $item->expires_on->lt(now()->startOfDay()))
                                    <x-badge tone="rose">{{ __('Expired') }}</x-badge>
                                @elseif ($item->isExpiringSoon())
                                    <x-badge tone="amber">{{ __('Expiring') }}</x-badge>
                                @else
                                    <x-badge tone="teal">{{ __('OK') }}</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="lab-actions">
                                    <x-btn :href="route('inventory-items.show', $item)" size="sm" variant="teal" icon="eye">{{ __('View') }}</x-btn>
                                    <x-btn :href="route('inventory-items.edit', $item)" size="sm" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
                                    <form method="POST" action="{{ route('inventory-items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-btn type="submit" size="sm" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3">{{ $items->links() }}</div>
        @endif
    </x-panel>
</x-layout>
