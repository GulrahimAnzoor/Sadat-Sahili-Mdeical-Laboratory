@props([
    'patient' => null,
    'compact' => false,
])

@php
    $unit = old('age_unit', $patient?->age_unit?->value ?? 'y');
    $isMonths = $unit === 'm';
    $labelClass = $compact
        ? 'mb-1 block text-xs font-medium text-slate-600 dark:text-slate-300'
        : 'mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300';
@endphp

<div data-age-field>
    <label for="age" class="{{ $labelClass }}">{{ __('Age') }}</label>
    <div class="flex items-stretch gap-1.5">
        <div class="relative min-w-0 flex-1">
            <input
                id="age"
                name="age"
                type="number"
                min="{{ $isMonths ? 1 : 0 }}"
                max="{{ $isMonths ? 23 : 120 }}"
                value="{{ old('age', $patient?->age) }}"
                data-age-input
                class="lab-input pe-8"
            >
            <span
                data-age-unit-label
                class="pointer-events-none absolute inset-y-0 end-0 flex w-8 items-center justify-center text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500"
            >{{ $isMonths ? 'm' : 'y' }}</span>
        </div>
        <input type="hidden" name="age_unit" value="y">
        <label
            for="age_unit"
            class="group inline-flex shrink-0 cursor-pointer select-none items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2.5 shadow-sm transition has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50 has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-teal-600/10 dark:border-slate-700 dark:bg-slate-950 dark:has-[:checked]:border-teal-400 dark:has-[:checked]:bg-teal-950/50"
            title="{{ __('Check for months when the infant is under one year') }}"
        >
            <input
                id="age_unit"
                name="age_unit"
                type="checkbox"
                value="m"
                data-age-months
                aria-label="{{ __('Infant') }}"
                @checked($isMonths)
                class="size-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600 dark:border-slate-600"
            >
            <span class="text-xs font-bold uppercase tracking-wide text-slate-600 group-has-[:checked]:text-teal-800 dark:text-slate-300 dark:group-has-[:checked]:text-teal-200">m</span>
        </label>
    </div>
    @error('age')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
    @error('age_unit')
        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>

@pushOnce('scripts')
    <script>
        document.querySelectorAll('[data-age-field]').forEach((field) => {
            const input = field.querySelector('[data-age-input]');
            const months = field.querySelector('[data-age-months]');
            const unit = field.querySelector('[data-age-unit-label]');

            if (!(input instanceof HTMLInputElement) || !(months instanceof HTMLInputElement)) {
                return;
            }

            const sync = () => {
                input.min = months.checked ? '1' : '0';
                input.max = months.checked ? '23' : '120';

                if (unit) {
                    unit.textContent = months.checked ? 'm' : 'y';
                }
            };

            months.addEventListener('change', sync);
            sync();
        });
    </script>
@endpushOnce
