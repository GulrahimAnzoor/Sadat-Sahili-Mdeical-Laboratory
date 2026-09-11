<x-layout :title="$test->name">
    <x-page-header :description="__('Test details, price, and normal range')">
        <x-slot:actions>
            <x-btn :href="route('patient-tests.create', ['test_id' => $test->id])" icon="flask">{{ __('Assign to patient') }}</x-btn>
            <x-btn :href="route('settings.test-reports.edit', $test)" variant="secondary" icon="report">{{ __('Range and summary') }}</x-btn>
            <x-btn :href="route('tests.edit', $test)" variant="secondary" icon="edit">{{ __('Edit') }}</x-btn>
            <form method="POST" action="{{ route('tests.destroy', $test) }}">
                @csrf
                @method('DELETE')
                <x-btn type="submit" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
            </form>
            <x-btn :href="route('tests.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <dl class="lab-card mb-6 grid gap-4 p-6 sm:grid-cols-4">
        <div>
            <dt class="text-sm text-slate-500">{{ __('Price') }}</dt>
            <dd class="mt-1 font-medium">{{ number_format((float) $test->price, 2) }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Department') }}</dt>
            <dd class="mt-1 font-medium">{{ $test->department->label() }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Reference range') }}</dt>
            <dd class="mt-1 font-medium whitespace-pre-wrap">{{ $test->normal_range }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Uses / Results') }}</dt>
            <dd class="mt-1 font-medium">{{ $test->patient_tests_count }} / {{ $test->test_results_count }}</dd>
        </div>
    </dl>

    <x-panel class="mb-6" :title="__('Test template')">
        <x-slot:subtitle>{{ __('Units, ranges and interpretation print automatically. The technician types RESULT only.') }}</x-slot:subtitle>
        <div class="px-5 py-4">
            <form method="POST" action="{{ route('tests.parameters.store', $test) }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                @csrf
                <input name="name" required class="lab-input lg:col-span-2" placeholder="{{ __('Parameter') }}">
                <input name="unit" class="lab-input" placeholder="{{ __('Unit') }}">
                <textarea name="normal_range" rows="2" class="lab-input" placeholder="{{ __('Reference range') }}">{{ old('normal_range') }}</textarea>
                <input name="group_name" class="lab-input" placeholder="{{ __('Group') }}">
                <x-btn type="submit">{{ __('Add test information') }}</x-btn>
            </form>
        </div>
        @if ($test->parameters->isEmpty())
            <p class="px-5 pb-8 text-sm text-slate-500">{{ __('No template rows yet.') }}</p>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($test->parameters as $parameter)
                    <li class="flex items-center justify-between gap-3 px-5 py-3">
                        <div>
                            <p class="font-medium">{{ $parameter->name }} <span class="text-xs text-slate-400">{{ $parameter->group_name }}</span></p>
                            <p class="whitespace-pre-wrap text-xs text-slate-500">{{ $parameter->unit }} · {{ $parameter->normal_range }}</p>
                        </div>
                        <form method="POST" action="{{ route('tests.parameters.destroy', [$test, $parameter]) }}">
                            @csrf
                            @method('DELETE')
                            <x-btn type="submit" size="sm" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-panel>

    <x-panel :title="__('Latest results')">
        @if ($test->testResults->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No result has been recorded for this test.') }}</p>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($test->testResults as $testResult)
                    <li class="flex items-center justify-between gap-3 px-5 py-3">
                        <a href="{{ route('patients.show', $testResult->patient) }}" class="font-medium text-slate-900 hover:text-teal-800 dark:text-white">{{ $testResult->patient->name }}</a>
                        <span class="text-sm font-semibold">{{ $testResult->result }} {{ $testResult->unit }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-panel>
</x-layout>
