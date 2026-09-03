<div>
    <label for="name" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Name') }}</label>
    <input id="name" name="name" type="text" value="{{ old('name', $test->name ?? '') }}" required class="lab-input">
    @error('name')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
<div>
    <label for="code" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Code') }}</label>
    <input id="code" name="code" type="text" value="{{ old('code', $test->code ?? '') }}" class="lab-input">
</div>
<div>
    <label for="department" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Department') }}</label>
    <select id="department" name="department" class="lab-input">
        @foreach ($departments as $department)
            <option value="{{ $department->value }}" @selected(old('department', isset($test) ? $test->department->value : 'routine') === $department->value)>{{ $department->label() }}</option>
        @endforeach
    </select>
</div>
<div>
    <label for="price" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Price') }}</label>
    <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $test->price ?? '') }}" required class="lab-input">
    @error('price')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
<div>
    <label for="normal_range" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Reference range') }}</label>
    <textarea id="normal_range" name="normal_range" rows="4" required class="lab-input">{{ old('normal_range', $test->normal_range ?? '') }}</textarea>
    <p class="mt-1 text-xs text-slate-500">{{ __('This text prints on the right of the result.') }}</p>
    @error('normal_range')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
<div class="flex items-center gap-2">
    <input id="is_active" name="is_active" type="hidden" value="0">
    <input id="is_active_checkbox" name="is_active" type="checkbox" value="1" @checked((bool) old('is_active', $test->is_active ?? true)) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
    <label for="is_active_checkbox" class="text-sm text-slate-700 dark:text-slate-300">{{ __('Active') }}</label>
</div>
<div>
    <label for="interpretation" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Summary') }}</label>
    <textarea id="interpretation" name="interpretation" rows="8" class="lab-input">{{ old('interpretation', $test->interpretation ?? '') }}</textarea>
    <p class="mt-1 text-xs text-slate-500">{{ __('This text prints below the result table.') }}</p>
</div>
<div>
    <label for="clinical_utility" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Clinical utility') }}</label>
    <textarea id="clinical_utility" name="clinical_utility" rows="2" class="lab-input">{{ old('clinical_utility', $test->clinical_utility ?? '') }}</textarea>
</div>
<div>
    <label for="method" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Method') }}</label>
    <input id="method" name="method" type="text" value="{{ old('method', $test->method ?? '') }}" class="lab-input">
</div>
