@php
    $oldRows = old('results.0.extra_rows');
    $starterRows = \App\Support\LabPanelTemplates::visibleEntryRows(
        $oldRows ?? $parameters->map(fn ($parameter) => [
            'name' => $parameter->name,
            'value' => $saved?->valueFor($parameter->id),
            'unit' => $parameter->unit,
            'normal_range' => $parameter->normal_range,
            'group_name' => $parameter->group_name,
        ])->all(),
        (string) ($test?->name ?? ''),
    );

    $groups = [
        \App\Support\LabPanelTemplates::Physical,
        \App\Support\LabPanelTemplates::Chemical,
        \App\Support\LabPanelTemplates::Microscopic,
    ];
    $grouped = $starterRows->groupBy(fn (array $row): string => $row['group_name'] ?? '');
    $extraIndex = 0;
@endphp

<div class="space-y-4">
    @foreach ($groups as $groupName)
        @php
            $groupRows = $grouped->get($groupName, collect());
        @endphp
        @if ($groupRows->isEmpty())
            @continue
        @endif
        <div data-row-group class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="border-b border-amber-200 bg-amber-50/80 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-amber-800 dark:border-amber-900 dark:bg-amber-950/20 dark:text-amber-200">
                {{ $groupName }}
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-800 dark:border-slate-700 dark:text-slate-100">
                            <th class="w-[32%] px-3 py-2 text-start text-xs font-bold uppercase">{{ __('Test') }}</th>
                            <th class="w-[28%] px-3 py-2 text-start text-xs font-bold uppercase">{{ __('Result') }}</th>
                            <th class="px-3 py-2 text-start text-xs font-bold uppercase">{{ __('Normal range') }}</th>
                            <th class="w-20 px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody data-result-rows class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($groupRows as $extraRow)
                            @include('visits.partials.result-urine-row', [
                                'extraIndex' => $extraIndex,
                                'extraRow' => $extraRow,
                                'groupName' => $groupName,
                            ])
                            @php $extraIndex++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
            <template data-extra-row-template>
                @include('visits.partials.result-urine-row', [
                    'extraIndex' => '__INDEX__',
                    'extraRow' => ['group_name' => $groupName],
                    'groupName' => $groupName,
                ])
            </template>
            <div class="border-t border-slate-100 px-3 py-2 dark:border-slate-800">
                <x-btn type="button" variant="secondary" size="sm" icon="plus" data-add-row>{{ __('Add row') }}</x-btn>
            </div>
        </div>
    @endforeach
</div>
