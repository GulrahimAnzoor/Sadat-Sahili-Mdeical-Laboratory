<x-layout :title="__('Report — :name', ['name' => $patient->name])" print="a4">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Lab report') }}</h1>
            <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $patient->name }} · {{ $visit->token_code }}</p>
        </div>
        <div class="lab-actions">
            <x-btn type="button" onclick="window.print()" icon="print">{{ __('Print All') }}</x-btn>
            <x-btn :href="route('visits.results.edit', $visit)" variant="secondary" icon="flask">{{ __('Fill') }}</x-btn>
            <x-btn :href="route('worklist')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </div>

    <div class="report-root">
        <article class="report-page">
            @include('reports._watermark')

            <header class="report-top">
                @include('reports._header', ['patient' => $patient, 'visit' => $visit])
            </header>

            <main class="report-main">
                @forelse ($visit->patientTests as $patientTest)
                    @include('reports._test-block', [
                        'patientTest' => $patientTest,
                        'testResult' => $resultsByTestId->get($patientTest->test_id),
                    ])
                @empty
                    <p class="report-empty">{{ __('No tests have been assigned.') }}</p>
                @endforelse
            </main>

            @include('reports._footer')
        </article>
    </div>
</x-layout>
