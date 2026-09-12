<x-layout :title="$patientTest->patient->name.' — '.$patientTest->test->name">
    <x-page-header :description="__('Patient: :name', ['name' => $patientTest->patient->name])">
        <x-slot:actions>
            @unless ($testResult)
                <x-btn :href="route('test-results.create', ['patient_id' => $patientTest->patient_id, 'test_id' => $patientTest->test_id])" icon="flask">{{ __('Record result') }}</x-btn>
            @endunless
            <x-btn :href="route('tokens.show', $patientTest->patient)" variant="secondary" icon="ticket">{{ __('Token') }}</x-btn>
            @can('records.edit')
                <x-btn :href="route('patient-tests.edit', $patientTest)" variant="secondary" icon="edit">{{ __('Edit') }}</x-btn>
            @endcan
            <x-btn :href="route('patients.show', $patientTest->patient)" variant="ghost" icon="user">{{ __('Patient page') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <dl class="mb-6 grid gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm sm:grid-cols-4">
        <div>
            <dt class="text-sm text-slate-500">{{ __('Test') }}</dt>
            <dd class="mt-1 font-medium">
                <a href="{{ route('tests.show', $patientTest->test) }}" class="text-teal-800 hover:text-teal-950">{{ $patientTest->test->name }}</a>
            </dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Price') }}</dt>
            <dd class="mt-1 font-medium">{{ number_format((float) $patientTest->total_price, 2) }}</dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Payment') }}</dt>
            <dd class="mt-1 flex items-center gap-3">
                <x-badge :tone="$patientTest->paid ? 'teal' : 'amber'">{{ $patientTest->paid ? __('Paid') : __('Unpaid') }}</x-badge>
                @unless ($patientTest->paid)
                    <form method="POST" action="{{ route('patient-tests.payment', $patientTest) }}">
                        @csrf
                        @method('PATCH')
                        <x-btn type="submit" size="sm" variant="amber">{{ __('Mark as paid') }}</x-btn>
                    </form>
                @endunless
            </dd>
        </div>
        <div>
            <dt class="text-sm text-slate-500">{{ __('Patient') }}</dt>
            <dd class="mt-1 font-medium">
                <a href="{{ route('patients.show', $patientTest->patient) }}" class="text-teal-800 hover:text-teal-950">{{ $patientTest->patient->name }}</a>
            </dd>
        </div>
    </dl>

    <x-panel :title="__('Result')">
        @if ($testResult)
            <div class="grid gap-4 px-5 py-5 sm:grid-cols-3">
                <div>
                    <p class="text-sm text-slate-500">{{ __('Outcome') }}</p>
                    <p class="mt-1 text-lg font-semibold">{{ $testResult->result }} {{ $testResult->unit }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">{{ __('Normal range') }}</p>
                    <p class="mt-1 font-medium">{{ $patientTest->test->normal_range }}</p>
                </div>
                <div class="flex items-end">
                    <x-btn :href="route('test-results.show', $testResult)" size="sm" variant="teal">{{ __('Result page') }}</x-btn>
                </div>
            </div>
        @else
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('Result has not been recorded yet.') }}</p>
        @endif
    </x-panel>
</x-layout>
