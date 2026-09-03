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
        @include('reports._chrome', ['patient' => $patient])

        @if ($patient->testResults->isEmpty())
            <article class="report-page">
                @include('reports._watermark')
                <div class="report-screen-chrome">
                    @include('reports._header', ['patient' => $patient])
                </div>
                <p class="report-empty">{{ __('No result has been recorded yet.') }}</p>
                <div class="report-screen-chrome">
                    @include('reports._footer')
                </div>
            </article>
        @else
            @foreach ($patient->testResults as $testResult)
                <article class="report-page">
                    @include('reports._watermark')
                    <div class="report-screen-chrome">
                        @include('reports._header', ['patient' => $patient])
                    </div>
                    @include('reports._test-block', ['patientTest' => null, 'testResult' => $testResult])
                    <div class="report-screen-chrome">
                        @include('reports._footer')
                    </div>
                </article>
            @endforeach
        @endif
    </div>
</x-layout>
