<x-layout :title="__('Lab tests')">
    @php
        $savedTests = $visit === null
            ? collect()
            : $visit->patientTests
                ->filter(fn ($patientTest) => $patientTest->test !== null)
                ->map(fn ($patientTest): array => [
                    'id' => $patientTest->test->id,
                    'name' => $patientTest->test->name,
                    'price' => (float) $patientTest->test->price,
                ])
                ->values();
        $testsSaved = $savedTests->isNotEmpty();
    @endphp

    <div class="lab-card mb-6 p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold tracking-wide text-teal-700">{{ $patient->file_number }}</p>
                <h2 class="text-xl font-semibold text-slate-900 dark:text-white">{{ $patient->name }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                    {{ __('Son/daughter of :name', ['name' => $patient->father_name]) }}
                    · {{ $patient->genderAndAgeLabel() }}
                    · {{ __('Ref By') }}: {{ $patient->referrerLabel() }}
                </p>
            </div>
            <x-btn :href="route('reception.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </div>

    <ol class="mb-6 grid gap-3 sm:grid-cols-3">
        <li id="step-choose" class="lab-card flex items-center gap-3 p-4 ring-2 ring-teal-600/30">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-teal-700 text-sm font-semibold text-white">1</span>
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Choose tests') }}</p>
                <p class="text-xs text-slate-500">{{ __('Search, tick, then save.') }}</p>
            </div>
        </li>
        <li id="step-discount" class="lab-card flex items-center gap-3 p-4">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">2</span>
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Save discount') }}</p>
                <p class="text-xs text-slate-500">{{ __('Totals fill after tests are saved.') }}</p>
            </div>
        </li>
        <li id="step-print" class="lab-card flex items-center gap-3 p-4">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-200 text-sm font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">3</span>
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Print tokens') }}</p>
                <p class="text-xs text-slate-500">{{ __('Appears after the discount is saved.') }}</p>
            </div>
        </li>
    </ol>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lab-card space-y-4 p-5 lg:col-span-2">
            <div>
                <label for="test-search" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Search tests') }}</label>
                <div id="test-combobox" class="relative">
                    <input id="test-search" type="search" class="lab-input pe-11" placeholder="{{ __('CBC, TFT, Urine R/E...') }}" autocomplete="off" aria-expanded="false" aria-controls="test-dropdown" aria-autocomplete="list">
                    <button id="test-dropdown-toggle" type="button" class="absolute inset-y-0 end-0 flex w-11 items-center justify-center text-slate-400 hover:text-teal-700" aria-label="{{ __('Open test list') }}">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                    </button>
                    <div id="test-dropdown" class="absolute z-20 mt-2 hidden max-h-80 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-900" role="group" aria-label="{{ __('Search tests') }}">
                        <div id="test-dropdown-list" class="space-y-3"></div>
                        <p id="test-dropdown-empty" class="hidden px-2 py-6 text-center text-sm text-slate-500">{{ __('No matching tests') }}</p>
                    </div>
                </div>
                <p class="mt-1 text-xs text-slate-500">{{ __('Search and tick tests. Selections stay until you save.') }}</p>
                <p id="test-picker-status" class="mt-1 hidden text-sm text-red-700"></p>
            </div>

            <div class="rounded-xl border border-dashed border-slate-200 p-4 dark:border-slate-700">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Selected tests') }}</p>
                    <span id="selected-count" class="rounded-full bg-teal-50 px-2 py-0.5 text-xs font-semibold text-teal-800 dark:bg-teal-950 dark:text-teal-300">0</span>
                </div>
                <ul id="selected-tests" class="space-y-2">
                    <li id="selected-empty" class="text-sm text-slate-400">{{ __('No tests selected yet. Search, tick, and they will appear here.') }}</li>
                </ul>
                <div class="mt-3 flex justify-between border-t border-dashed border-slate-200 pt-3 text-sm dark:border-slate-700">
                    <span class="text-slate-500">{{ __('Selected total') }}</span>
                    <span id="selected-total" class="font-semibold">0.00</span>
                </div>
            </div>

            <x-btn type="button" id="save-tests" icon="save" class="w-full disabled:cursor-not-allowed disabled:opacity-50" disabled>{{ __('Save tests') }}</x-btn>
        </div>

        <div class="space-y-6">
            <div id="discount-card" class="lab-card space-y-4 p-5">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Discount') }}</p>
                <p id="discount-hint" class="text-xs text-slate-500">{{ __('Save the selected tests first.') }}</p>
                <div class="flex justify-between border-t border-dashed border-slate-200 pt-3 text-sm dark:border-slate-700">
                    <span class="text-slate-500">{{ __('Subtotal') }}</span>
                    <span id="fee-subtotal" class="font-semibold">{{ number_format((float) ($visit?->subtotal ?? 0), 2) }}</span>
                </div>
                <div>
                    <label for="discount_percent" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Discount') }} %</label>
                    <input id="discount_percent" type="number" min="0" max="100" step="0.01" value="{{ old('discount_percent', $visit?->discount_percent ?? 0) }}" class="lab-input" @disabled(! $testsSaved)>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">{{ __('Discount amount') }}</span>
                    <span id="fee-discount" class="font-semibold">{{ number_format((float) ($visit?->discount_amount ?? 0), 2) }}</span>
                </div>
                <div class="flex justify-between border-t border-dashed border-slate-200 pt-3 text-sm font-semibold dark:border-slate-700">
                    <span>{{ __('Net') }}</span>
                    <span id="fee-total">{{ number_format((float) ($visit?->total ?? 0), 2) }}</span>
                </div>
                <x-btn type="button" id="save-discount" icon="save" class="w-full disabled:cursor-not-allowed disabled:opacity-50" disabled>{{ __('Save discount') }}</x-btn>
            </div>

            <div id="print-step" class="lab-card hidden space-y-4 p-5">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Print tokens') }}</p>
                <p class="text-xs text-slate-500">{{ __('Discount saved. Print the tokens.') }}</p>
                <form id="print-form" method="POST" action="{{ $visit ? route('visits.print', $visit) : '#' }}" class="space-y-4">
                    @csrf
                    <div class="flex items-center gap-2">
                        <input id="paid" name="paid" type="checkbox" value="1" @checked((bool) old('paid', true)) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                        <label for="paid" class="text-sm text-slate-700 dark:text-slate-300">{{ __('Payment received') }}</label>
                    </div>
                    <input type="hidden" name="discount_percent" id="print-discount" value="{{ $visit?->discount_percent ?? 0 }}">
                    <x-btn id="print-button" type="submit" class="w-full" icon="print">{{ __('Print tokens') }}</x-btn>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
<script>
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const catalogue = @js($catalogue);
    const savedTests = @js($savedTests);
    const search = document.getElementById('test-search');
    const combobox = document.getElementById('test-combobox');
    const dropdown = document.getElementById('test-dropdown');
    const dropdownList = document.getElementById('test-dropdown-list');
    const dropdownEmpty = document.getElementById('test-dropdown-empty');
    const dropdownToggle = document.getElementById('test-dropdown-toggle');
    const subtotalEl = document.getElementById('fee-subtotal');
    const discountEl = document.getElementById('fee-discount');
    const totalEl = document.getElementById('fee-total');
    const discountInput = document.getElementById('discount_percent');
    const printDiscount = document.getElementById('print-discount');
    const printForm = document.getElementById('print-form');
    const printStep = document.getElementById('print-step');
    const selectedList = document.getElementById('selected-tests');
    const selectedEmpty = document.getElementById('selected-empty');
    const selectedCount = document.getElementById('selected-count');
    const selectedTotal = document.getElementById('selected-total');
    const statusEl = document.getElementById('test-picker-status');
    const saveTestsButton = document.getElementById('save-tests');
    const saveDiscountButton = document.getElementById('save-discount');
    const discountHint = document.getElementById('discount-hint');
    const stepChoose = document.getElementById('step-choose');
    const stepDiscount = document.getElementById('step-discount');
    const stepPrint = document.getElementById('step-print');
    const syncUrl = @js(route('reception.tests.sync', $patient));
    const billingUrl = @js(route('visits.billing.update', ['visit' => $visit?->id ?? 0]));
    const printUrl = @js(route('visits.print', ['visit' => $visit?->id ?? 0]));
    const removeLabel = @js(__('Remove'));
    let visitId = @js($visit?->id);
    let testsSaved = @js($testsSaved);
    let discountSaved = false;
    let savedSubtotal = Number(@js((float) ($visit?->subtotal ?? 0)));
    const selected = new Map();

    savedTests.forEach((test) => selected.set(String(test.id), test));

    function money(value) {
        return Number(value || 0).toFixed(2);
    }

    function el(tag, className) {
        const node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        return node;
    }

    function showStatus(message) {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = message || '';
        statusEl.classList.toggle('hidden', !message);
    }

    function selectedIds() {
        return [...selected.keys()].map(Number).sort((a, b) => a - b);
    }

    function savedIds() {
        return savedTests.map((test) => Number(test.id)).sort((a, b) => a - b);
    }

    function selectionMatchesSaved() {
        const current = selectedIds();
        const stored = savedIds();
        return current.length === stored.length && current.every((id, index) => id === stored[index]);
    }

    function setStep(node, active) {
        node.classList.toggle('ring-2', active);
        node.classList.toggle('ring-teal-600/30', active);
        const badge = node.querySelector('span');
        if (!badge) {
            return;
        }
        badge.classList.toggle('bg-teal-700', active);
        badge.classList.toggle('text-white', active);
        badge.classList.toggle('bg-slate-200', !active);
        badge.classList.toggle('text-slate-600', !active);
        badge.classList.toggle('dark:bg-slate-800', !active);
        badge.classList.toggle('dark:text-slate-300', !active);
    }

    function updateSteps() {
        setStep(stepChoose, !testsSaved || !selectionMatchesSaved());
        setStep(stepDiscount, testsSaved && selectionMatchesSaved() && !discountSaved);
        setStep(stepPrint, discountSaved && selectionMatchesSaved());
    }

    function previewDiscount() {
        const percent = Number(discountInput?.value || 0);
        const amount = Math.round((savedSubtotal * percent / 100) * 100) / 100;
        const total = Math.round(Math.max(0, savedSubtotal - amount) * 100) / 100;
        if (discountEl) discountEl.textContent = money(amount);
        if (totalEl) totalEl.textContent = money(total);
        if (printDiscount) printDiscount.value = percent;
    }

    function applyTotals(data) {
        visitId = data.visit_id;
        savedSubtotal = Number(data.subtotal || 0);
        if (subtotalEl) subtotalEl.textContent = money(data.subtotal);
        if (discountEl) discountEl.textContent = money(data.discount_amount);
        if (totalEl) totalEl.textContent = money(data.total);
        if (discountInput && document.activeElement !== discountInput) {
            discountInput.value = data.discount_percent;
        }
        if (printDiscount) printDiscount.value = data.discount_percent;
        if (printForm && visitId) {
            printForm.action = printUrl.replace(/\/0\/print$/, '/' + visitId + '/print');
        }
        previewDiscount();
    }

    function updateButtons() {
        const hasSelection = selected.size > 0;
        const matches = testsSaved && selectionMatchesSaved();
        if (saveTestsButton) {
            saveTestsButton.disabled = !hasSelection;
        }
        if (discountInput) {
            discountInput.disabled = !matches;
        }
        if (saveDiscountButton) {
            saveDiscountButton.disabled = !matches;
        }
        if (discountHint) {
            discountHint.textContent = matches
                ? @js(__('Totals fill after tests are saved.'))
                : @js(__('Save the selected tests first.'));
        }
        if (printStep) {
            printStep.classList.toggle('hidden', !(discountSaved && matches));
        }
        updateSteps();
    }

    function renderSelected() {
        selectedList.querySelectorAll('[data-selected]').forEach((row) => row.remove());
        const items = [...selected.values()];
        if (selectedEmpty) selectedEmpty.classList.toggle('hidden', items.length > 0);
        if (selectedCount) selectedCount.textContent = String(items.length);
        if (selectedTotal) {
            selectedTotal.textContent = money(items.reduce((sum, test) => sum + Number(test.price), 0));
        }
        items.forEach((test) => {
            const item = el('li', 'flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2 dark:bg-slate-800');
            item.dataset.selected = '1';
            const name = el('span', 'min-w-0 truncate text-sm font-medium text-slate-800 dark:text-slate-100');
            name.textContent = test.name;
            const meta = el('span', 'flex shrink-0 items-center gap-2');
            const price = el('span', 'text-xs text-slate-500');
            price.textContent = money(test.price);
            const remove = el('button', 'inline-flex items-center rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-200/80 hover:bg-rose-100 dark:bg-rose-950/60 dark:text-rose-300');
            remove.type = 'button';
            remove.textContent = removeLabel;
            remove.addEventListener('click', () => {
                selected.delete(String(test.id));
                discountSaved = false;
                renderSelected();
                renderDropdown();
                updateButtons();
            });
            meta.append(price, remove);
            item.append(name, meta);
            selectedList.append(item);
        });
        updateButtons();
    }

    function filteredCatalogue() {
        const query = (search?.value || '').trim().toLowerCase();
        return catalogue.filter((test) => !query || String(test.name).toLowerCase().includes(query));
    }

    function renderDropdown() {
        dropdownList.replaceChildren();
        const matches = filteredCatalogue();
        dropdownEmpty.classList.toggle('hidden', matches.length > 0);
        const groups = new Map();
        matches.forEach((test) => {
            const key = test.department || '';
            if (!groups.has(key)) {
                groups.set(key, []);
            }
            groups.get(key).push(test);
        });
        groups.forEach((tests, department) => {
            const group = el('div', '');
            if (department) {
                const heading = el('p', 'mb-1 px-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400');
                heading.textContent = department;
                group.append(heading);
            }
            tests.forEach((test) => {
                const row = el('label', 'flex cursor-pointer items-center justify-between gap-3 rounded-lg px-2 py-1.5 hover:bg-teal-50 dark:hover:bg-slate-800');
                const left = el('span', 'flex min-w-0 items-center gap-2 text-sm');
                const box = document.createElement('input');
                box.type = 'checkbox';
                box.value = String(test.id);
                box.checked = selected.has(String(test.id));
                box.className = 'rounded border-slate-300 text-teal-700 focus:ring-teal-600';
                box.addEventListener('change', () => {
                    if (box.checked) {
                        selected.set(String(test.id), test);
                        if (search) {
                            search.value = '';
                            search.focus();
                        }
                        renderDropdown();
                    } else {
                        selected.delete(String(test.id));
                    }
                    discountSaved = false;
                    renderSelected();
                });
                const name = el('span', 'truncate');
                name.textContent = test.name;
                left.append(box, name);
                const price = el('span', 'shrink-0 text-xs text-slate-500');
                price.textContent = money(test.price);
                row.append(left, price);
                group.append(row);
            });
            dropdownList.append(group);
        });
    }

    function openDropdown() {
        dropdown.classList.remove('hidden');
        search?.setAttribute('aria-expanded', 'true');
        renderDropdown();
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        search?.setAttribute('aria-expanded', 'false');
    }

    async function send(url, method, body) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body ? JSON.stringify(body) : null,
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = data.message || Object.values(data.errors || {})[0]?.[0] || @js(__('Could not save the test.'));
            throw new Error(message);
        }
        return data;
    }

    search?.addEventListener('focus', openDropdown);
    search?.addEventListener('input', () => {
        openDropdown();
    });
    dropdownToggle?.addEventListener('click', (event) => {
        event.preventDefault();
        if (dropdown.classList.contains('hidden')) {
            openDropdown();
            search?.focus();
        } else {
            closeDropdown();
        }
    });
    document.addEventListener('click', (event) => {
        if (combobox && !combobox.contains(event.target)) {
            closeDropdown();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdown();
        }
    });

    saveTestsButton?.addEventListener('click', async () => {
        showStatus('');
        saveTestsButton.disabled = true;
        try {
            const data = await send(syncUrl, 'PUT', { test_ids: selectedIds() });
            savedTests.splice(0, savedTests.length, ...[...selected.values()]);
            testsSaved = true;
            discountSaved = false;
            applyTotals(data);
            closeDropdown();
            updateButtons();
            window.labFlash?.(data.message || @js(__('Tests saved.')), 'success');
        } catch (error) {
            showStatus(error.message);
            window.labFlash?.(error.message || @js(__('The action could not be completed.')), 'error');
            updateButtons();
        }
    });

    saveDiscountButton?.addEventListener('click', async () => {
        showStatus('');
        if (!visitId) {
            return;
        }
        saveDiscountButton.disabled = true;
        try {
            const data = await send(billingUrl.replace(/\/0\/billing$/, '/' + visitId + '/billing'), 'PATCH', {
                discount_percent: discountInput.value || 0,
            });
            discountSaved = true;
            applyTotals(data);
            updateButtons();
            window.labFlash?.(data.message || @js(__('Discount saved.')), 'success');
        } catch (error) {
            showStatus(error.message);
            window.labFlash?.(error.message || @js(__('The action could not be completed.')), 'error');
            updateButtons();
        }
    });

    discountInput?.addEventListener('input', () => {
        discountSaved = false;
        previewDiscount();
        updateButtons();
    });

    renderSelected();
</script>
@endpush
</x-layout>
