<x-layout :title="__('Report ranges and summaries')">
    <x-page-header :description="__('Select a test, then paste its range and summary. Printing shows the result on top, the range on the right, and the summary below.')">
        <x-slot:actions>
            <x-settings-back />
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-[20rem_minmax(0,1fr)]">
        <x-panel :title="__('Tests')">
            <div class="border-b border-slate-100 px-5 py-3 dark:border-slate-800">
                <label for="test-report-search" class="sr-only">{{ __('Search tests') }}</label>
                <input id="test-report-search" type="search" class="lab-input" placeholder="{{ __('Search tests') }}" autocomplete="off">
            </div>
            @if ($tests->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No tests have been registered.') }}</p>
            @else
                <ul class="max-h-[70vh] overflow-y-auto">
                    @foreach ($tests as $test)
                        <li data-test-name="{{ str($test->name)->lower() }}">
                            <a
                                href="{{ route('settings.test-reports.edit', $test) }}"
                                class="flex items-center justify-between gap-3 px-5 py-3 text-sm transition hover:bg-teal-50 dark:hover:bg-teal-950/40 {{ $selected?->is($test) ? 'bg-teal-50 font-semibold text-teal-900 dark:bg-teal-950/50 dark:text-teal-100' : 'text-slate-700 dark:text-slate-200' }}"
                            >
                                <span>{{ $test->name }}</span>
                                @if (filled($test->interpretation))
                                    <x-badge tone="teal">{{ __('Has summary') }}</x-badge>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-panel>

        @if ($selected)
            <div class="flex flex-col gap-6">
                <x-panel :title="$selected->name">
                    <x-slot:subtitle>{{ __('The technician types the result only. Range and summary print automatically.') }}</x-slot:subtitle>
                    <form id="report-content-form" method="POST" action="{{ route('settings.test-reports.update', $selected) }}" class="space-y-5 p-5">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="normal_range" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Reference range') }}</label>
                            <textarea id="normal_range" name="normal_range" rows="6" required class="lab-input" data-preview-range>{{ old('normal_range', $selected->normal_range) }}</textarea>
                            <p class="mt-1 text-xs text-slate-500">{{ __('This text prints on the right of the result.') }}</p>
                            @error('normal_range')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($selected->parameters->isNotEmpty())
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Parameter ranges') }}</p>
                                @foreach ($selected->parameters as $index => $parameter)
                                    <div class="grid gap-2 sm:grid-cols-[10rem_minmax(0,1fr)] sm:items-start">
                                        <label for="parameter-range-{{ $parameter->id }}" class="pt-2 text-sm text-slate-600 dark:text-slate-400">
                                            {{ $parameter->name }}
                                            @if (filled($parameter->unit))
                                                <span class="text-xs text-slate-400">{{ $parameter->unit }}</span>
                                            @endif
                                        </label>
                                        <div>
                                            <input type="hidden" name="parameters[{{ $index }}][id]" value="{{ $parameter->id }}">
                                            <textarea
                                                id="parameter-range-{{ $parameter->id }}"
                                                name="parameters[{{ $index }}][normal_range]"
                                                rows="2"
                                                class="lab-input"
                                                data-parameter-range-input="{{ $parameter->id }}"
                                            >{{ old('parameters.'.$index.'.normal_range', $parameter->normal_range) }}</textarea>
                                            @error('parameters.'.$index.'.normal_range')
                                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div>
                            <label for="interpretation" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Summary') }}</label>
                            <textarea id="interpretation" name="interpretation" rows="10" class="lab-input" data-preview-summary>{{ old('interpretation', $selected->interpretation) }}</textarea>
                            <p class="mt-1 text-xs text-slate-500">{{ __('This text prints below the result table.') }}</p>
                            @error('interpretation')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-btn type="submit" icon="save">{{ __('Save') }}</x-btn>
                    </form>
                </x-panel>

                <x-panel :title="__('Print preview')">
                    <div class="p-5">
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950">
                            <h3 class="report-test-title mx-0!">
                                {{ collect([$selected->department?->label(), $selected->name])->filter()->unique()->implode(' ') }}
                            </h3>
                            <table class="report-table mx-0! w-full!">
                                <thead>
                                    <tr>
                                        <th>{{ __('Test') }}</th>
                                        <th>{{ __('Result') }}</th>
                                        <th>{{ __('Units') }}</th>
                                        <th>{{ __('Normal range') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($selected->parameters->isNotEmpty())
                                        @foreach ($selected->parameters as $parameter)
                                            <tr>
                                                <td>{{ $parameter->name }}</td>
                                                <td class="report-result">—</td>
                                                <td>{{ $parameter->unit }}</td>
                                                <td class="report-range" data-preview-parameter-range="{{ $parameter->id }}">{{ $parameter->normal_range }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td>{{ $selected->name }}</td>
                                            <td class="report-result">—</td>
                                            <td></td>
                                            <td class="report-range" data-preview-test-range>{{ $selected->normal_range }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            <section class="report-summary mx-0!" data-preview-summary-wrap @if (! filled($selected->interpretation)) hidden @endif>
                                <h4>{{ __('Summary') }}</h4>
                                <p data-preview-summary-text>{{ $selected->interpretation }}</p>
                            </section>
                        </div>
                    </div>
                </x-panel>
            </div>
        @else
            <x-panel>
                <p class="px-5 py-16 text-center text-sm text-slate-500">{{ __('Choose a test from the list to add its reference range and summary.') }}</p>
            </x-panel>
        @endif
    </div>

    @pushOnce('scripts')
        <script>
            (() => {
                const search = document.getElementById('test-report-search');

                search?.addEventListener('input', () => {
                    const query = search.value.trim().toLowerCase();

                    document.querySelectorAll('[data-test-name]').forEach((row) => {
                        row.hidden = query !== '' && !row.dataset.testName.includes(query);
                    });
                });

                const form = document.getElementById('report-content-form');

                if (!form) {
                    return;
                }

                const syncPreview = () => {
                    const rangeInput = form.querySelector('[data-preview-range]');
                    const testRange = document.querySelector('[data-preview-test-range]');

                    if (rangeInput && testRange) {
                        testRange.textContent = rangeInput.value;
                    }

                    form.querySelectorAll('[data-parameter-range-input]').forEach((input) => {
                        const cell = document.querySelector(`[data-preview-parameter-range="${input.dataset.parameterRangeInput}"]`);

                        if (cell) {
                            cell.textContent = input.value;
                        }
                    });

                    const summaryInput = form.querySelector('[data-preview-summary]');
                    const summaryText = document.querySelector('[data-preview-summary-text]');
                    const summaryWrap = document.querySelector('[data-preview-summary-wrap]');

                    if (summaryInput && summaryText && summaryWrap) {
                        const summary = summaryInput.value.trim();
                        summaryText.textContent = summaryInput.value;
                        summaryWrap.hidden = summary === '';
                    }
                };

                form.addEventListener('input', syncPreview);
                syncPreview();
            })();
        </script>
    @endpushOnce
</x-layout>
