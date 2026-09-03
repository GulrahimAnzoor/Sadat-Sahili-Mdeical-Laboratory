<div>
    <label for="patient_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Patient') }}</label>
    <select id="patient_id" name="patient_id" required class="lab-input">
        <option value="">{{ __('Select') }}</option>
        @foreach ($patients as $patient)
            <option value="{{ $patient->id }}" @selected((string) old('patient_id', $patientTest->patient_id ?? request('patient_id')) === (string) $patient->id)>{{ $patient->name }}{{ $patient->file_number ? ' · '.$patient->file_number : '' }}</option>
        @endforeach
    </select>
    @error('patient_id')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>

@if (isset($patientTest))
    <div>
        <label for="test_id" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Test') }}</label>
        <select id="test_id" name="test_id" required class="lab-input">
            <option value="">{{ __('Select') }}</option>
            @foreach ($tests as $test)
                <option value="{{ $test->id }}" @selected((string) old('test_id', $patientTest->test_id) === (string) $test->id)>{{ $test->name }} — {{ number_format((float) $test->price, 2) }}</option>
            @endforeach
        </select>
        @error('test_id')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="total_price" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Price') }}</label>
        <input id="total_price" name="total_price" type="number" step="0.01" min="0" value="{{ old('total_price', $patientTest->total_price) }}" class="lab-input">
        @error('total_price')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
    </div>
@else
    <div>
        <label for="test-search" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Search tests') }}</label>
        <input id="test-search" type="search" class="lab-input" placeholder="{{ __('CBC, TFT, Urine R/E...') }}" autocomplete="off">
        @error('test_ids')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
        @error('test_id')
            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
        @enderror
        <p id="fee-total" class="mt-2 text-sm font-semibold text-teal-800 dark:text-teal-300">{{ __('Fee') }}: 0.00</p>
        <div id="test-picker" class="mt-3 max-h-72 space-y-3 overflow-y-auto rounded-xl border border-slate-200 p-3 dark:border-slate-700">
            @php($selected = collect(old('test_ids', request()->filled('test_id') ? [request('test_id')] : []))->map(fn ($id) => (string) $id))
            @foreach ($tests->groupBy(fn ($test) => $test->department->label()) as $department => $group)
                <div>
                    <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ $department }}</p>
                    <div class="space-y-1">
                        @foreach ($group as $test)
                            <label class="test-option flex cursor-pointer items-center justify-between gap-3 rounded-lg px-2 py-1.5 hover:bg-teal-50 dark:hover:bg-slate-800" data-name="{{ $test->name }}">
                                <span class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="test_ids[]" value="{{ $test->id }}" data-price="{{ $test->price }}" @checked($selected->contains((string) $test->id)) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                                    {{ $test->name }}
                                </span>
                                <span class="text-xs text-slate-500">{{ number_format((float) $test->price, 2) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="flex items-center gap-2">
    <input id="paid" name="paid" type="checkbox" value="1" @checked((bool) old('paid', $patientTest->paid ?? false)) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
    <label for="paid" class="text-sm text-slate-700 dark:text-slate-300">{{ __('Payment received') }}</label>
</div>
