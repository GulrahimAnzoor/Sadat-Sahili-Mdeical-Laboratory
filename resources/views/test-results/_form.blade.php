@php
    $record = $testResult ?? null;
    $sensitivityRows = old('sensitivities', $record?->sensitivities?->toArray() ?: [['antibiotic' => '', 'sensitivity' => 'S']]);
@endphp

<div>
    <label for="patient_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Patient') }}</label>
    <select id="patient_id" name="patient_id" required class="lab-input">
        <option value="">{{ __('Select') }}</option>
        @foreach ($patients as $patient)
            <option value="{{ $patient->id }}" @selected((string) old('patient_id', $record->patient_id ?? request('patient_id')) === (string) $patient->id)>{{ $patient->name }}</option>
        @endforeach
    </select>
    @error('patient_id')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
<div>
    <label for="test_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Test') }}</label>
    <select id="test_id" name="test_id" required class="lab-input">
        <option value="">{{ __('Select') }}</option>
        @foreach ($tests as $test)
            <option value="{{ $test->id }}" data-unit="{{ $test->parameters->first()?->unit }}" @selected((string) old('test_id', $record->test_id ?? request('test_id')) === (string) $test->id)>{{ $test->name }}</option>
        @endforeach
    </select>
    @error('test_id')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
<div>
    <label for="result" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Result') }}</label>
    <input id="result" name="result" type="text" value="{{ old('result', $record->result ?? '') }}" required class="lab-input" autofocus>
    @error('result')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
    <p class="mt-1 text-xs text-slate-500">{{ __('Enter result only. Unit and range come from the test template.') }}</p>
</div>
<div>
    <label for="unit" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Unit') }}</label>
    <input id="unit" name="unit" type="text" value="{{ old('unit', $record->unit ?? '') }}" required class="lab-input">
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Organism') }}</label>
        <input name="organism" type="text" value="{{ old('organism', $record->organism ?? '') }}" class="lab-input">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Colony count') }}</label>
        <input name="colony_count" type="text" value="{{ old('colony_count', $record->colony_count ?? '') }}" class="lab-input">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Gram stain') }}</label>
        <input name="gram_stain" type="text" value="{{ old('gram_stain', $record->gram_stain ?? '') }}" class="lab-input">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Specimen') }}</label>
        <input name="specimen" type="text" value="{{ old('specimen', $record->specimen ?? '') }}" class="lab-input">
    </div>
</div>
<div>
    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Culture method') }}</label>
    <input name="culture_method" type="text" value="{{ old('culture_method', $record->culture_method ?? '') }}" class="lab-input">
</div>
<div class="space-y-2">
    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Antibiotic sensitivity') }}</p>
    @foreach ($sensitivityRows as $index => $row)
        <div class="grid grid-cols-2 gap-2">
            <input name="sensitivities[{{ $index }}][antibiotic]" class="lab-input" placeholder="{{ __('Antibiotic') }}" value="{{ is_array($row) ? ($row['antibiotic'] ?? '') : '' }}">
            <select name="sensitivities[{{ $index }}][sensitivity]" class="lab-input">
                @foreach (['S' => __('Sensitive'), 'I' => __('Intermediate'), 'R' => __('Resistant')] as $value => $label)
                    <option value="{{ $value }}" @selected((is_array($row) ? ($row['sensitivity'] ?? 'S') : 'S') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endforeach
</div>
