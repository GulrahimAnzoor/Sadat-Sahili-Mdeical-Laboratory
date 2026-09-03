<div>
    <label for="name" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Name') }}</label>
    <input id="name" name="name" type="text" value="{{ old('name', $doctor->name ?? '') }}" required class="lab-input">
    @error('name')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
<div>
    <label for="specialty" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Specialty') }}</label>
    <input id="specialty" name="specialty" type="text" value="{{ old('specialty', $doctor->specialty ?? '') }}" class="lab-input">
</div>
<div>
    <label for="clinic" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Clinic') }}</label>
    <input id="clinic" name="clinic" type="text" value="{{ old('clinic', $doctor->clinic ?? '') }}" class="lab-input">
</div>
<div class="flex items-center gap-2">
    <input type="hidden" name="is_contracted" value="0">
    <input id="is_contracted" name="is_contracted" type="checkbox" value="1" @checked((bool) old('is_contracted', $doctor->is_contracted ?? true)) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
    <label for="is_contracted" class="text-sm text-slate-700 dark:text-slate-300">{{ __('Contracted doctor') }}</label>
</div>
