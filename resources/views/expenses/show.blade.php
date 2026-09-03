<x-layout :title="$expense->title">
    <x-page-header>
        <x-slot:actions>
            <x-btn :href="route('expenses.edit', $expense)" variant="secondary" icon="edit">{{ __('Edit') }}</x-btn>
            <x-btn :href="route('expenses.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </x-slot:actions>
    </x-page-header>
    <dl class="lab-card grid gap-4 p-6 sm:grid-cols-3">
        <div><dt class="text-sm text-slate-500">{{ __('Category') }}</dt><dd>{{ __($expense->category) }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Amount') }}</dt><dd class="font-semibold">{{ number_format((float) $expense->amount, 2) }}</dd></div>
        <div><dt class="text-sm text-slate-500">{{ __('Date') }}</dt><dd>{{ $expense->spent_on->toDateString() }}</dd></div>
    </dl>
</x-layout>
