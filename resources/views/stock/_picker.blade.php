@props([
    'namePrefix',
    'lots',
    'rows' => [],
    'formId' => null,
])

@php
    $rowCount = collect($rows)->count();
@endphp

<div class="p-4 sm:p-5" data-stock-picker>
    <div class="rounded-2xl border border-teal-100 bg-teal-50/60 p-3 dark:border-teal-900/60 dark:bg-teal-950/30">
        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-teal-800 dark:text-teal-300">{{ __('Add from stock') }}</label>
        <div class="grid gap-2">
            <input type="search" data-stock-search class="lab-input" placeholder="{{ __('Type a name…') }}" autocomplete="off">
            <select data-stock-select class="lab-input">
                <option value="">{{ __('Select an item') }}</option>
                @foreach ($lots as $lot)
                    <option
                        value="{{ $lot->id }}"
                        data-name="{{ $lot->name }}"
                        data-qty="{{ $lot->quantity }}"
                        data-batch="{{ $lot->batch_number }}"
                        data-expiry="{{ $lot->expires_on?->toDateString() }}"
                        data-search="{{ strtolower($lot->name.' '.$lot->batch_number) }}"
                    >
                        {{ $lot->lotLabel() }}
                    </option>
                @endforeach
            </select>
        </div>
        <p class="mt-2 text-xs leading-5 text-slate-600 dark:text-slate-400">{{ __('Each selected item becomes its own row with name and quantity.') }}</p>
    </div>

    <div class="mt-4 flex items-center justify-between gap-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Selected materials') }}</p>
        <span data-stock-count class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $rowCount }}</span>
    </div>

    <div data-stock-rows class="mt-2 space-y-2">
        @foreach ($rows as $index => $row)
            @include('stock._usage-row', [
                'namePrefix' => $namePrefix,
                'index' => $index,
                'row' => $row,
                'formId' => $formId,
            ])
        @endforeach
    </div>

    <p data-stock-empty class="mt-2 rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500 dark:border-slate-700 {{ $rowCount > 0 ? 'hidden' : '' }}">
        {{ __('No materials added yet.') }}
    </p>

    <template data-stock-row-template>
        @include('stock._usage-row', [
            'namePrefix' => $namePrefix,
            'index' => '__INDEX__',
            'row' => [],
            'formId' => $formId,
        ])
    </template>
</div>

@once
    @push('scripts')
    <script>
        document.querySelectorAll('[data-stock-picker]').forEach((picker) => {
            const search = picker.querySelector('[data-stock-search]');
            const select = picker.querySelector('[data-stock-select]');
            const rows = picker.querySelector('[data-stock-rows]');
            const template = picker.querySelector('[data-stock-row-template]');
            const empty = picker.querySelector('[data-stock-empty]');
            const count = picker.querySelector('[data-stock-count]');

            const refresh = () => {
                const total = rows?.querySelectorAll('[data-stock-row]').length ?? 0;
                empty?.classList.toggle('hidden', total > 0);
                if (count) {
                    count.textContent = String(total);
                }
            };

            search?.addEventListener('input', () => {
                const term = search.value.trim().toLowerCase();
                select.querySelectorAll('option[data-search]').forEach((option) => {
                    option.hidden = term !== '' && ! option.dataset.search.includes(term);
                });
            });

            select?.addEventListener('change', () => {
                const option = select.selectedOptions[0];

                if (! option?.value || ! rows || ! template) {
                    return;
                }

                const index = rows.querySelectorAll('[data-stock-row]').length;
                const html = template.innerHTML.replaceAll('__INDEX__', String(index));
                rows.insertAdjacentHTML('beforeend', html);

                const row = rows.querySelector('[data-stock-row]:last-child');
                row.querySelector('[data-stock-id]').value = option.value;
                row.querySelector('[data-stock-name]').textContent = option.dataset.name || '';
                row.querySelector('[data-stock-onhand]').textContent = option.dataset.qty || '0';
                row.querySelector('[data-stock-expiry]').textContent = option.dataset.expiry || '—';
                const batch = row.querySelector('[data-stock-batch]');
                if (batch) {
                    batch.textContent = option.dataset.batch || '—';
                }
                row.querySelector('[data-stock-qty]').value = '1';
                row.querySelector('[data-stock-qty]').focus();
                select.value = '';
                refresh();
            });

            refresh();
        });

        document.addEventListener('click', (event) => {
            const button = event.target instanceof Element ? event.target.closest('[data-stock-remove]') : null;

            if (! button) {
                return;
            }

            const picker = button.closest('[data-stock-picker]');
            button.closest('[data-stock-row]')?.remove();

            const rows = picker?.querySelector('[data-stock-rows]');
            const empty = picker?.querySelector('[data-stock-empty]');
            const count = picker?.querySelector('[data-stock-count]');
            const total = rows?.querySelectorAll('[data-stock-row]').length ?? 0;
            empty?.classList.toggle('hidden', total > 0);
            if (count) {
                count.textContent = String(total);
            }
        });
    </script>
    @endpush
@endonce
