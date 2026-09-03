<div>
    <label for="name" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Name') }}</label>
    <input id="name" name="name" type="text" value="{{ old('name', $patient->name ?? '') }}" required class="lab-input">
    @error('name')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
<div>
    <label for="father_name" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __("Father's name") }}</label>
    <input id="father_name" name="father_name" type="text" value="{{ old('father_name', $patient->father_name ?? '') }}" required class="lab-input">
    @error('father_name')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="gender" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Gender') }}</label>
        <select id="gender" name="gender" required class="lab-input">
            <option value="">{{ __('Select') }}</option>
            <option value="male" @selected(old('gender', $patient->gender ?? '') === 'male')>{{ __('Male') }}</option>
            <option value="female" @selected(old('gender', $patient->gender ?? '') === 'female')>{{ __('Female') }}</option>
        </select>
        @error('gender')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>
    <x-age-field :patient="$patient ?? null" />
</div>
<div>
    <label for="phone" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Phone') }}</label>
    <input id="phone" name="phone" type="text" value="{{ old('phone', $patient->phone ?? '') }}" class="lab-input">
</div>
<div>
    <label for="doctor_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Ref By') }}</label>
    <select id="doctor_id" name="doctor_id" class="lab-input">
        <option value="">{{ __('Self request') }}</option>
        @foreach ($doctors as $doctor)
            <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $patient->doctor_id ?? request('doctor_id')) === (string) $doctor->id)>{{ $doctor->name }}{{ $doctor->clinic ? ' · '.$doctor->clinic : '' }}</option>
        @endforeach
    </select>
    @error('doctor_id')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
@unless ($compact ?? false)
    <div>
        <label for="address" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Address') }}</label>
        <input id="address" name="address" type="text" value="{{ old('address', $patient->address ?? '') }}" class="lab-input">
    </div>
    <div>
        <label for="description" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Notes') }}</label>
        <textarea id="description" name="description" rows="3" class="lab-input">{{ old('description', $patient->description ?? '') }}</textarea>
    </div>
@endunless
