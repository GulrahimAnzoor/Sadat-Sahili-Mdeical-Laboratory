<x-layout :title="$testResult->patient->name.' — '.$testResult->test->name">
    <x-page-header :description="__('Patient: :name', ['name' => $testResult->patient->name])">
        <x-slot:actions>
            <x-btn :href="route('reports.show', $testResult->patient)" icon="report">{{ __('Report') }}</x-btn>
            @can('records.edit')
                <x-btn :href="route('test-results.edit', $testResult)" variant="secondary" icon="edit">{{ __('Edit') }}</x-btn>
            @endcan
            <x-btn :href="route('patients.show', $testResult->patient)" variant="ghost" icon="user">{{ __('Patient page') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <dl class="grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:grid-cols-4">
        <div>
            <dt class="text-sm text-slate-500">{{ __('Test') }}</dt>
            <dd class="mt-1 font-medium">
                <a href="{{ route('tests.show', $testResult->test) }}" class="text-teal-800 hover:text-teal-950">{{ $testResult->test->name }}</a>
            </dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Result') }}</dt>
            <dd class="mt-1 text-lg font-semibold">{{ $testResult->result }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Unit') }}</dt>
            <dd class="mt-1 font-medium">{{ $testResult->unit }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Normal range') }}</dt>
            <dd class="mt-1 font-medium">{{ $testResult->test->normal_range }}</dd>
        </div>
    </dl>
</x-layout>
