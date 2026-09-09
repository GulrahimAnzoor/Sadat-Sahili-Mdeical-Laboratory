<x-layout :title="$visit->patient->name">
    @php
        $patient = $visit->patient;
        $filled = $visit->patientTests->filter(
            fn ($patientTest) => $resultsByTestId->has($patientTest->test_id)
        )->count();
        $total = $visit->patientTests->count();
    @endphp

    <div class="lab-card mb-6 p-5 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wide text-teal-700 dark:text-teal-300">{{ $patient->file_number }} · {{ $visit->token_code }}</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ $patient->name }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                    {{ __('Son/daughter of :name', ['name' => $patient->father_name]) }}
                    · {{ $patient->genderAndAgeLabel() }}
                    · {{ __('Ref By') }}: {{ $visit->referrerLabel() }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-btn :href="route('visits.report', $visit)" variant="secondary" icon="print">{{ __('Print All') }}</x-btn>
                <x-btn :href="route('worklist')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
            </div>
        </div>
        <dl class="mt-5 grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-4 dark:border-slate-800">
            <div>
                <dt class="text-xs text-slate-500">{{ __('File no.') }}</dt>
                <dd class="mt-1 font-medium">{{ $patient->file_number ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">{{ __('Queue no.') }}</dt>
                <dd class="mt-1 font-medium">{{ $visit->queue_number ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">{{ __('Phone') }}</dt>
                <dd class="mt-1 font-medium">{{ $patient->phone ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs text-slate-500">{{ __('Results') }}</dt>
                <dd class="mt-1 font-medium">{{ $filled }}/{{ $total }} {{ __('Filled') }}</dd>
            </div>
        </dl>
    </div>

    <div class="grid gap-6 lg:grid-cols-12">
        <aside class="lg:col-span-4">
            <x-panel :title="__('Ordered tests')">
                <x-slot:subtitle>{{ __('Select a test from this visit to enter results.') }}</x-slot:subtitle>
                <div class="space-y-2 p-3" role="tablist" aria-label="{{ __('Ordered tests') }}">
                    @foreach ($visit->patientTests as $patientTest)
                        @php
                            $saved = $resultsByTestId->get($patientTest->test_id);
                            $isActive = (int) $activeTestId === (int) $patientTest->id;
                        @endphp
                        <button
                            type="button"
                            id="test-tab-{{ $patientTest->id }}"
                            data-test-trigger="{{ $patientTest->id }}"
                            role="tab"
                            aria-selected="{{ $isActive ? 'true' : 'false' }}"
                            aria-controls="test-panel-{{ $patientTest->id }}"
                            class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-3 text-start transition hover:bg-teal-50 dark:hover:bg-slate-800 {{ $isActive ? 'bg-teal-50 ring-2 ring-teal-600/30 dark:bg-teal-950/40' : '' }}"
                        >
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $patientTest->test?->name }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">
                                    {{ $patientTest->test?->parameters?->count() ?? 0 }} {{ __('Parameters') }}
                                    · {{ $patientTest->test?->department?->label() }}
                                </span>
                            </span>
                            <x-badge :tone="$saved ? 'teal' : 'amber'">{{ $saved ? __('Filled') : __('Pending') }}</x-badge>
                        </button>
                    @endforeach
                </div>
            </x-panel>

            @foreach ($visit->patientTests as $patientTest)
                @php
                    $isActive = (int) $activeTestId === (int) $patientTest->id;
                    $oldMaterials = $isActive ? old('results.0.materials') : null;
                    $materialRows = $oldMaterials !== null
                        ? collect($oldMaterials)
                        : ($materialsByPatientTestId->get($patientTest->id) ?? collect())->map(fn ($movement) => [
                            'inventory_item_id' => $movement->inventory_item_id,
                            'quantity' => $movement->quantity,
                            'item' => $movement->inventoryItem,
                        ]);
                @endphp
                <div
                    data-test-panel="{{ $patientTest->id }}"
                    class="mt-6 {{ $isActive ? '' : 'hidden' }}"
                >
                    <x-panel :title="__('Materials used')">
                        <x-slot:subtitle>{{ __('Search stock, pick an item, then enter how much was used.') }}</x-slot:subtitle>
                        <input type="hidden" form="visit-result-form-{{ $patientTest->id }}" name="results[0][consume_materials]" value="1">
                        @include('stock._picker', [
                            'namePrefix' => 'results[0][materials]',
                            'lots' => $stockLots,
                            'rows' => $materialRows,
                            'formId' => 'visit-result-form-'.$patientTest->id,
                        ])
                    </x-panel>
                    @error('quantity')
                        <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </aside>

        <div class="lg:col-span-8 space-y-6">
            @foreach ($visit->patientTests as $patientTest)
                @php
                    $saved = $resultsByTestId->get($patientTest->test_id);
                    $isActive = (int) $activeTestId === (int) $patientTest->id;
                    $parameters = $patientTest->test?->parameters ?? collect();
                @endphp
                <section
                    id="test-panel-{{ $patientTest->id }}"
                    data-test-panel="{{ $patientTest->id }}"
                    role="tabpanel"
                    aria-labelledby="test-tab-{{ $patientTest->id }}"
                    class="{{ $isActive ? '' : 'hidden' }}"
                >
                    <form method="POST" action="{{ route('visits.results.store', $visit) }}" id="visit-result-form-{{ $patientTest->id }}" class="lab-card overflow-hidden">
                        @csrf
                        <input type="hidden" name="results[0][patient_test_id]" value="{{ $patientTest->id }}">
                        <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                            <p class="text-xs font-semibold tracking-wide text-teal-700 dark:text-teal-300">{{ __('Result entry') }}</p>
                            <h3 class="mx-auto mt-3 max-w-xl rounded-lg border-2 border-amber-600 px-4 py-2 text-center text-base font-bold text-slate-900 dark:border-amber-500 dark:text-white">
                                {{ collect([$patientTest->test?->department?->label(), $patientTest->test?->name])->filter()->unique()->implode(' ') }}
                            </h3>
                            <p class="mt-3 text-sm text-slate-500">{{ __('Enter each result on its own row. Add a row when this test has more than one value.') }}</p>
                        </div>

                        <div class="space-y-4 p-5">
                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="border-b-2 border-slate-800 text-slate-800 dark:border-slate-200 dark:text-slate-100">
                                            <th class="w-[22%] px-3 py-2 text-start text-xs font-bold uppercase underline">{{ __('Test') }}</th>
                                            <th class="w-[16%] px-3 py-2 text-start text-xs font-bold uppercase underline">{{ __('Result') }}</th>
                                            <th class="w-[16%] px-3 py-2 text-start text-xs font-bold uppercase underline">{{ __('Units') }}</th>
                                            <th class="px-3 py-2 text-start text-xs font-bold uppercase underline">{{ __('Normal range') }}</th>
                                            <th class="w-20 px-3 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody data-result-rows class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @if ($parameters->isNotEmpty())
                                            @php
                                                $valueIndex = 0;
                                            @endphp
                                            @foreach ($parameters->groupBy(fn ($parameter) => $parameter->group_name ?: '') as $group => $groupParameters)
                                                @if ($group !== '')
                                                    <tr class="bg-amber-50/80 dark:bg-amber-950/20">
                                                        <td colspan="5" class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-amber-800 dark:text-amber-200">{{ $group }}</td>
                                                    </tr>
                                                @endif
                                                @foreach ($groupParameters as $parameter)
                                                    <tr>
                                                        <td class="px-3 py-2 align-top font-medium text-slate-900 dark:text-white">
                                                            <input type="hidden" name="results[0][values][{{ $valueIndex }}][test_parameter_id]" value="{{ $parameter->id }}">
                                                            {{ $parameter->name }}
                                                        </td>
                                                        <td class="px-3 py-2 align-top">
                                                            <input
                                                                name="results[0][values][{{ $valueIndex }}][value]"
                                                                value="{{ old('results.0.values.'.$valueIndex.'.value', $saved?->valueFor($parameter->id)) }}"
                                                                data-normal-range="{{ $parameter->normal_range }}"
                                                                class="lab-input"
                                                                autocomplete="off"
                                                            >
                                                        </td>
                                                        <td class="px-3 py-2 align-top text-slate-700 dark:text-slate-200">{{ $parameter->unit ?: '—' }}</td>
                                                        <td class="whitespace-pre-wrap px-3 py-2 align-top text-slate-600 dark:text-slate-300">{{ $parameter->normal_range ?: '—' }}</td>
                                                        <td class="px-3 py-2"></td>
                                                    </tr>
                                                    @php
                                                        $valueIndex++;
                                                    @endphp
                                                @endforeach
                                            @endforeach
                                            @foreach (old('results.0.extra_rows', []) as $extraIndex => $extraRow)
                                                @include('visits.partials.result-extra-row', ['extraIndex' => $extraIndex, 'extraRow' => $extraRow])
                                            @endforeach
                                        @else
                                            @php
                                                $starterRows = old('results.0.extra_rows') ?? [[
                                                    'name' => $patientTest->test?->name,
                                                    'value' => $saved?->result,
                                                    'unit' => $saved?->unit,
                                                    'normal_range' => $patientTest->test?->normal_range,
                                                ]];
                                            @endphp
                                            @foreach ($starterRows as $extraIndex => $extraRow)
                                                @include('visits.partials.result-extra-row', ['extraIndex' => $extraIndex, 'extraRow' => $extraRow])
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <template data-extra-row-template>
                                @include('visits.partials.result-extra-row', ['extraIndex' => '__INDEX__', 'extraRow' => []])
                            </template>

                            <div class="flex flex-wrap items-center gap-3">
                                <x-btn type="button" variant="secondary" size="sm" icon="plus" data-add-row>{{ __('Add row') }}</x-btn>
                                <p class="text-xs text-slate-500">{{ __('If this test has several results, add a row for each one.') }}</p>
                            </div>

                            @if (filled($patientTest->test?->interpretation))
                                <div class="rounded-xl border border-dashed border-slate-200 px-4 py-3 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:text-slate-300">
                                    {{ $patientTest->test->interpretation }}
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end border-t border-slate-100 px-5 py-4 dark:border-slate-800">
                            <x-btn type="submit" icon="save">{{ __('Save result') }}</x-btn>
                        </div>
                    </form>
                </section>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        const triggers = document.querySelectorAll('[data-test-trigger]');
        const panels = document.querySelectorAll('[data-test-panel]');

        function showTest(id) {
            triggers.forEach((button) => {
                const active = button.dataset.testTrigger === String(id);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
                button.classList.toggle('ring-2', active);
                button.classList.toggle('ring-teal-600/30', active);
                button.classList.toggle('bg-teal-50', active);
                button.classList.toggle('dark:bg-teal-950/40', active);
            });
            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.testPanel !== String(id));
            });
        }

        triggers.forEach((button) => {
            button.addEventListener('click', () => showTest(button.dataset.testTrigger));
        });

        function flagRange(input) {
            const range = input.dataset.normalRange || '';
            const match = range.match(/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)/);
            const numeric = input.value !== '' && !Number.isNaN(Number(input.value));
            const abnormal = Boolean(match && numeric && (Number(input.value) < Number(match[1]) || Number(input.value) > Number(match[2])));
            input.classList.toggle('ring-2', abnormal);
            input.classList.toggle('ring-rose-400', abnormal);
            input.classList.toggle('text-rose-700', abnormal);
        }

        document.querySelectorAll('[data-normal-range]').forEach((input) => {
            input.addEventListener('input', () => flagRange(input));
            flagRange(input);
        });

        document.querySelectorAll('[data-add-row]').forEach((button) => {
            button.addEventListener('click', () => {
                const form = button.closest('form');
                const container = form?.querySelector('[data-result-rows]');
                const template = form?.querySelector('[data-extra-row-template]');

                if (! container || ! template) {
                    return;
                }

                const index = container.querySelectorAll('[data-extra-row]').length;
                const html = template.innerHTML.replaceAll('__INDEX__', String(index));
                container.insertAdjacentHTML('beforeend', html);
            });
        });

        document.addEventListener('click', (event) => {
            const button = event.target instanceof Element ? event.target.closest('[data-remove-row]') : null;

            if (! button) {
                return;
            }

            button.closest('[data-extra-row]')?.remove();
        });
    </script>
    @endpush
</x-layout>
