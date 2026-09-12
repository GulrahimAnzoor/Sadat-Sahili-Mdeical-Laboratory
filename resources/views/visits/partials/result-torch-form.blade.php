@php
    $oldRows = old('results.0.extra_rows');
    $starterRows = $oldRows ?? $parameters->map(fn ($parameter) => [
        'name' => $parameter->name,
        'value' => $saved?->valueFor($parameter->id),
        'unit' => $parameter->unit,
        'normal_range' => $parameter->normal_range,
        'group_name' => $parameter->group_name ?: 'IgM',
    ])->all();
@endphp

<div data-row-group class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
    <table class="min-w-full text-sm">
        <thead>
            <tr class="border-b-2 border-slate-800 text-slate-800 dark:border-slate-200 dark:text-slate-100">
                <th class="w-[18%] px-3 py-2 text-start text-xs font-bold uppercase underline">{{ __('Test') }}</th>
                <th class="w-[12%] px-3 py-2 text-start text-xs font-bold uppercase underline">IgM</th>
                <th class="w-[16%] px-3 py-2 text-start text-xs font-bold uppercase underline">{{ __('Result') }}</th>
                <th class="w-[14%] px-3 py-2 text-start text-xs font-bold uppercase underline">{{ __('Units') }}</th>
                <th class="px-3 py-2 text-start text-xs font-bold uppercase underline">{{ __('Normal range') }}</th>
                <th class="w-20 px-3 py-2"></th>
            </tr>
        </thead>
        <tbody data-result-rows class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach ($starterRows as $extraIndex => $extraRow)
                @include('visits.partials.result-torch-row', [
                    'extraIndex' => $extraIndex,
                    'extraRow' => $extraRow,
                    'groupName' => 'IgM',
                ])
            @endforeach
        </tbody>
    </table>
    <template data-extra-row-template>
        @include('visits.partials.result-torch-row', [
            'extraIndex' => '__INDEX__',
            'extraRow' => ['group_name' => 'IgM'],
            'groupName' => 'IgM',
        ])
    </template>
    <div class="border-t border-slate-100 px-3 py-2 dark:border-slate-800">
        <x-btn type="button" variant="secondary" size="sm" icon="plus" data-add-row>{{ __('Add row') }}</x-btn>
    </div>
</div>
