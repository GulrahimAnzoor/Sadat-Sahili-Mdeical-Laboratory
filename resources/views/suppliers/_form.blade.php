<div>
    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Name') }}</label>
    <input name="name" required class="lab-input" value="{{ old('name', $supplier->name ?? '') }}">
    @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
<div>
    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Phone') }}</label>
    <input name="phone" class="lab-input" value="{{ old('phone', $supplier->phone ?? '') }}">
</div>
<div>
    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Address') }}</label>
    <input name="address" class="lab-input" value="{{ old('address', $supplier->address ?? '') }}">
</div>
