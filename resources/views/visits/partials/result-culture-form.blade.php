@php
    $antibiotics = \App\Support\LabPanelTemplates::antibioticsFor((string) ($test->name ?? ''));
    $savedSensitivities = $saved?->sensitivities
        ?->map(fn ($row) => [
            'antibiotic' => $row->antibiotic,
            'sensitivity' => $row->sensitivity?->value ?? $row->sensitivity,
        ])
        ->all() ?? [];

    $sensitivityRows = old('results.0.sensitivities') ?? ($savedSensitivities !== []
        ? $savedSensitivities
        : array_map(fn (string $name): array => ['antibiotic' => $name, 'sensitivity' => ''], $antibiotics));
@endphp

@include('visits.partials.result-panel-form', [
    'parameters' => $parameters,
    'saved' => $saved,
    'test' => $test,
])

<div data-sensitivity-group class="space-y-3 rounded-xl border border-slate-200 p-4 dark:border-slate-700">
    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ __('Antibiotic sensitivity') }}</p>
    <div data-sensitivity-rows class="space-y-2">
        @foreach ($sensitivityRows as $index => $row)
            @include('visits.partials.result-sensitivity-row', ['index' => $index, 'row' => $row])
        @endforeach
    </div>
    <template data-sensitivity-template>
        @include('visits.partials.result-sensitivity-row', ['index' => '__INDEX__', 'row' => ['antibiotic' => '', 'sensitivity' => '']])
    </template>
    <x-btn type="button" variant="secondary" size="sm" icon="plus" data-add-sensitivity>{{ __('Add row') }}</x-btn>
</div>
