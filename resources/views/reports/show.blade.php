<x-layout :title="__('Report — :name', ['name' => $patient->name])" print="a4">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Lab report') }}</h1>
            <p class="mt-1 text-slate-600 dark:text-slate-400">{{ $patient->name }}</p>
        </div>
        <div class="lab-actions">
            <x-btn type="button" onclick="window.print()" icon="print">{{ __('Print') }}</x-btn>
            <x-btn :href="route('reports.index')" variant="secondary" icon="report">{{ __('All reports') }}</x-btn>
            <x-btn :href="route('patients.show', $patient)" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </div>

    <div class="report-root">
        <article class="report-page">
            @include('reports._watermark')

            <header class="report-top">
                @include('reports._header', ['patient' => $patient])
            </header>

            <main class="report-main">
                @forelse ($patient->testResults as $testResult)
                    @include('reports._test-block', ['patientTest' => null, 'testResult' => $testResult])
                @empty
                    <p class="report-empty">{{ __('No result has been recorded yet.') }}</p>
                @endforelse
            </main>

            @include('reports._footer')
        </article>
    </div>
</x-layout>
