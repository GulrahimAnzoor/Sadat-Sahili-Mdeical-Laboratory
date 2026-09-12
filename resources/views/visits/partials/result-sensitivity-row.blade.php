@php
    $antibiotic = $row['antibiotic'] ?? '';
    $sensitivity = $row['sensitivity'] ?? '';
@endphp
<div data-sensitivity-row class="grid grid-cols-[minmax(0,1fr)_10rem_auto] items-center gap-2">
    <input
        name="results[0][sensitivities][{{ $index }}][antibiotic]"
        class="lab-input"
        placeholder="{{ __('Antibiotic') }}"
        value="{{ $antibiotic }}"
        autocomplete="off"
    >
    <select name="results[0][sensitivities][{{ $index }}][sensitivity]" class="lab-input">
        <option value="">{{ __('Select') }}</option>
        @foreach (\App\Enums\Sensitivity::cases() as $option)
            <option value="{{ $option->value }}" @selected($sensitivity === $option->value)>{{ $option->label() }}</option>
        @endforeach
    </select>
    <button type="button" data-remove-sensitivity class="inline-flex items-center justify-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold leading-5 text-rose-700 ring-1 ring-inset ring-rose-200/80 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-300 dark:ring-rose-900">{{ __('Remove') }}</button>
</div>
