<x-layout :title="__('Purchases')">
    <x-page-header :description="__('Supplier bills with previous balance, received and remaining')">
        <x-slot:actions>
            <x-settings-back />
            <x-btn :href="route('finance.index')" variant="ghost">{{ __('Finance') }}</x-btn>
            <x-btn :href="route('purchases.create')" icon="plus">{{ __('New purchase') }}</x-btn>
        </x-slot:actions>
    </x-page-header>
    <x-panel>
        @if ($purchases->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No purchases yet.') }}</p>
        @else
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800"><tr>
                    <th class="px-5 py-3 text-start">{{ __('Bill no.') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Supplier') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Date') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Total') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Remaining') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Actions') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($purchases as $purchase)
                        <tr>
                            <td class="px-5 py-3"><a class="font-medium text-teal-800 dark:text-teal-300" href="{{ route('purchases.show', $purchase) }}">{{ $purchase->bill_number }}</a></td>
                            <td class="px-5 py-3">{{ $purchase->supplier->name }}</td>
                            <td class="px-5 py-3">{{ $purchase->billed_on->toDateString() }}</td>
                            <td class="px-5 py-3">{{ number_format((float) $purchase->subtotal, 2) }}</td>
                            <td class="px-5 py-3">{{ number_format((float) $purchase->remaining, 2) }}</td>
                            <td class="px-5 py-3">
                                <div class="lab-actions">
                                    <x-btn :href="route('purchases.show', $purchase)" size="sm" variant="teal" icon="eye">{{ __('View') }}</x-btn>
                                    @can('records.edit')
                                        <x-btn :href="route('purchases.edit', $purchase)" size="sm" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
                                    @endcan
                                    @can('records.delete')
                                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-btn type="submit" size="sm" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3">{{ $purchases->links() }}</div>
        @endif
    </x-panel>
</x-layout>
