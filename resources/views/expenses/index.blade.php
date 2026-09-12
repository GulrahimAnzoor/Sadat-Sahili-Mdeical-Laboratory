<x-layout :title="__('Expenses')">
    <x-page-header :description="__('Cash costs such as rent, meals, salary and electricity. Laboratory stock usage is recorded from Inventory.')">
        <x-slot:actions>
            <x-settings-back />
            <x-btn :href="route('finance.index')" variant="ghost">{{ __('Finance') }}</x-btn>
            <x-btn :href="route('expenses.create')" icon="plus">{{ __('New expense') }}</x-btn>
        </x-slot:actions>
    </x-page-header>
    <x-panel>
        @if ($expenses->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No expenses yet.') }}</p>
        @else
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800"><tr>
                    <th class="px-5 py-3 text-start">{{ __('Title') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Category') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Amount') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Date') }}</th>
                    <th class="px-5 py-3 text-start">{{ __('Actions') }}</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($expenses as $expense)
                        <tr>
                            <td class="px-5 py-3"><a class="font-medium text-teal-800 dark:text-teal-300" href="{{ route('expenses.show', $expense) }}">{{ $expense->title }}</a></td>
                            <td class="px-5 py-3">{{ $expense->category->label() }}</td>
                            <td class="px-5 py-3">{{ number_format((float) $expense->amount, 2) }}</td>
                            <td class="px-5 py-3">{{ $expense->spent_on->toDateString() }}</td>
                            <td class="px-5 py-3">
                                <div class="lab-actions">
                                    <x-btn :href="route('expenses.show', $expense)" size="sm" variant="teal" icon="eye">{{ __('View') }}</x-btn>
                                    @can('records.edit')
                                        <x-btn :href="route('expenses.edit', $expense)" size="sm" variant="slate" icon="edit">{{ __('Edit') }}</x-btn>
                                    @endcan
                                    @can('records.delete')
                                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}">
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
            <div class="px-5 py-3">{{ $expenses->links() }}</div>
        @endif
    </x-panel>
</x-layout>
