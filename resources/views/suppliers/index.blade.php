<x-layout :title="__('Suppliers')">
    <x-page-header :description="__('Companies that supply reagents and materials')">
        <x-slot:actions>
            <x-settings-back />
            <x-btn :href="route('suppliers.create')" icon="plus">{{ __('New supplier') }}</x-btn>
        </x-slot:actions>
    </x-page-header>
    <x-panel>
        @if ($suppliers->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No suppliers yet.') }}</p>
        @else
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800"><tr>
                    <th class="px-5 py-3 text-start">{{ __('Name') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Phone') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Balance') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Actions') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <td class="px-5 py-3"><a class="font-medium text-teal-800 dark:text-teal-300" href="{{ route('suppliers.show', $supplier) }}">{{ $supplier->name }}</a></td>
                            <td class="px-5 py-3">{{ $supplier->phone ?: '—' }}</td>
                            <td class="px-5 py-3">{{ number_format((float) $supplier->current_balance, 2) }}</td>
                            <td class="px-5 py-3">
                                <div class="lab-actions">
                                    <x-btn :href="route('purchases.create', ['supplier_id' => $supplier->id])" size="sm" variant="teal" icon="cart">{{ __('Purchase') }}</x-btn>
                                    <x-btn :href="route('suppliers.edit', $supplier)" size="sm" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-3">{{ $suppliers->links() }}</div>
        @endif
    </x-panel>
</x-layout>
