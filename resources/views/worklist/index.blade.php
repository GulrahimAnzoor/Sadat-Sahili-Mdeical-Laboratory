<x-layout :title="__('Lab')">
    <x-page-header :description="__('Search the queue, then open the patient to fill results.')">
        <x-slot:actions>
            <form method="GET" action="{{ route('worklist') }}" class="flex gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search by name, file no., token or queue') }}" class="lab-input w-64 sm:w-80" autocomplete="off">
                <x-btn type="submit" variant="secondary" icon="search">{{ __('Search') }}</x-btn>
            </form>
            <x-btn :href="route('reception.index')" variant="ghost" icon="back">{{ __('Reception') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    @if ($visits->isEmpty())
        <div class="lab-card px-5 py-16 text-center">
            <p class="text-sm text-slate-500">
                @if (request()->filled('q'))
                    {{ __('No matching lab visits.') }}
                @else
                    {{ __('No patients are waiting in the lab.') }}
                @endif
            </p>
        </div>
    @else
        <div class="grid gap-4">
            @foreach ($visits as $visit)
                @php
                    $complete = $visit->isComplete();
                    $filled = $visit->patientTests->filter(
                        fn ($patientTest) => $visit->testResults->contains('test_id', $patientTest->test_id)
                    )->count();
                    $total = $visit->patientTests->count();
                @endphp
                <a href="{{ route('visits.results.edit', $visit) }}" class="lab-card block p-5 transition duration-200 ease-out hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md hover:ring-2 hover:ring-teal-600/20 dark:hover:border-teal-700">
                    <div class="flex flex-wrap items-start gap-4">
                        <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-50 to-sky-50 text-lg font-bold text-teal-800 dark:from-teal-950 dark:to-sky-950 dark:text-teal-200">
                            {{ $visit->queue_number ?: '—' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold tracking-wide text-teal-700 dark:text-teal-300">{{ $visit->patient->file_number }} · {{ $visit->token_code }}</p>
                            <h2 class="mt-0.5 text-lg font-semibold text-slate-900 dark:text-white">{{ $visit->patient->name }}</h2>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                                {{ __('Son/daughter of :name', ['name' => $visit->patient->father_name]) }}
                                · {{ $visit->patient->genderAndAgeLabel() }}
                                · {{ __('Ref By') }}: {{ $visit->referrerLabel() }}
                            </p>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($visit->patientTests as $patientTest)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $patientTest->test?->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-2">
                            <x-badge :tone="$complete ? 'teal' : 'amber'">{{ $complete ? __('Completed') : __('Pending') }}</x-badge>
                            <p class="text-xs text-slate-500">{{ $filled }}/{{ $total }} {{ __('Filled') }}</p>
                            <span class="text-sm font-medium text-teal-700 dark:text-teal-300">{{ __('Enter results') }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-4">{{ $visits->links() }}</div>
    @endif
</x-layout>
