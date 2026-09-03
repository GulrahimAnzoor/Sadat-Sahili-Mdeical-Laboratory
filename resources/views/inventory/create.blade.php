<x-layout :title="__('New item')">
    <section class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-sky-800 via-teal-700 to-cyan-600 px-6 py-7 text-white shadow-lg sm:px-8">
        <div class="pointer-events-none absolute -top-16 -end-10 size-48 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-20 -start-8 size-40 rounded-full bg-cyan-300/10"></div>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm text-sky-100">{{ __('Inventory') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('New item') }}</h2>
                <p class="mt-2 max-w-2xl text-sm text-sky-50">{{ __('Choose a category, then add item names in the rows below.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('settings.view')
                    <x-btn :href="route('settings.goods.index')" variant="glass" size="sm">{{ __('Item names') }}</x-btn>
                @endcan
                <x-btn :href="route('inventory-items.create')" variant="glass" size="sm" icon="plus">{{ __('New entry') }}</x-btn>
                <x-btn :href="route('inventory-items.index')" variant="glass" size="sm" icon="back">{{ __('Back') }}</x-btn>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-teal-200 bg-teal-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-teal-900 dark:bg-teal-950/40">
            <p class="text-sm font-medium text-teal-900 dark:text-teal-100">{{ session('success') }} {{ __('Ready for another category.') }}</p>
            <x-btn :href="route('inventory-items.create')" size="sm" icon="plus">{{ __('New entry') }}</x-btn>
        </div>
    @endif

    <form id="inventory-sheet" method="POST" action="{{ route('inventory-items.store') }}" class="space-y-5">
        @csrf

        <div class="lab-card p-5 sm:p-6">
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-1">
                    <label for="inventory-category" class="mb-1 block text-sm font-medium">{{ __('Category') }} <span class="text-rose-600">*</span></label>
                    <div id="inventory-category-combobox" class="relative">
                        <input
                            id="inventory-category"
                            name="category"
                            required
                            autocomplete="off"
                            list="inventory-category-list"
                            class="lab-input pe-11"
                            value="{{ old('category') }}"
                            placeholder="{{ __('Type or choose a category') }}"
                            aria-expanded="false"
                            aria-controls="inventory-category-dropdown"
                            aria-autocomplete="list"
                        >
                        <button id="inventory-category-toggle" type="button" class="absolute inset-y-0 end-0 flex w-11 items-center justify-center text-slate-400 hover:text-teal-700 dark:hover:text-teal-300" aria-label="{{ __('Open category list') }}">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                        </button>
                        <div id="inventory-category-dropdown" class="absolute z-20 mt-2 hidden max-h-64 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg dark:border-slate-700 dark:bg-slate-900" role="listbox" aria-label="{{ __('Category') }}"></div>
                    </div>
                    <datalist id="inventory-category-list">
                        @foreach ($categories as $category)
                            <option value="{{ $category }}"></option>
                        @endforeach
                    </datalist>
                    @error('category')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="inventory-received-on" class="mb-1 block text-sm font-medium">{{ __('Date') }} <span class="text-rose-600">*</span></label>
                    <input id="inventory-received-on" name="received_on" type="date" required class="lab-input" value="{{ old('received_on', now()->toDateString()) }}">
                    @error('received_on')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="inventory-supplier" class="mb-1 block text-sm font-medium">{{ __('Supplier') }} <span class="text-rose-600">*</span></label>
                    <select id="inventory-supplier" name="supplier_id" required class="lab-input">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="lab-card overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 dark:border-slate-800">
                <div>
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Items') }}</p>
                    <p class="text-xs text-slate-500">{{ __('Add a name and quantity on each row. Use Add row for another item.') }}</p>
                </div>
                <button id="inventory-add-row" type="button" class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-800 ring-1 ring-inset ring-sky-200/80 hover:bg-sky-100 dark:bg-sky-950/50 dark:text-sky-200 dark:ring-sky-800">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" /></svg>
                    {{ __('Add row') }}
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="lab-sheet">
                    <thead>
                        <tr>
                            <th class="w-12">#</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Qty') }}</th>
                            <th>{{ __('Minimum') }}</th>
                            <th>{{ __('Unit cost') }}</th>
                            <th>{{ __('Batch') }}</th>
                            <th>{{ __('Expiry') }}</th>
                            <th class="text-end">{{ __('Line total') }}</th>
                            <th class="w-12"><span class="sr-only">{{ __('Delete') }}</span></th>
                        </tr>
                    </thead>
                    <tbody id="inventory-lot-body">
                        @foreach ($lots as $index => $lot)
                            <tr data-lot-row>
                                <td class="lab-sheet-index text-center text-xs font-semibold text-slate-400">{{ $index + 1 }}</td>
                                <td>
                                    <div class="relative min-w-40" data-lot-name-box>
                                        <input name="lots[{{ $index }}][name]" class="lab-input pe-11" value="{{ $lot['name'] }}" data-lot-name list="inventory-item-name-list" placeholder="{{ __('Item name') }}" autocomplete="off">
                                        <button type="button" class="absolute inset-y-0 end-0 flex w-11 items-center justify-center text-slate-400 hover:text-teal-700 dark:hover:text-teal-300" data-lot-name-toggle aria-label="{{ __('Open item list') }}">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                                        </button>
                                        <div class="absolute z-20 mt-2 hidden max-h-64 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg dark:border-slate-700 dark:bg-slate-900" data-lot-name-menu></div>
                                    </div>
                                </td>
                                <td><input name="lots[{{ $index }}][quantity]" type="number" step="0.01" min="0" class="lab-input" value="{{ $lot['quantity'] }}" data-lot-qty></td>
                                <td><input name="lots[{{ $index }}][min_quantity]" type="number" step="0.01" min="0" class="lab-input" value="{{ $lot['min_quantity'] }}"></td>
                                <td><input name="lots[{{ $index }}][unit_cost]" type="number" step="0.01" min="0" class="lab-input" value="{{ $lot['unit_cost'] }}" data-lot-cost></td>
                                <td><input name="lots[{{ $index }}][batch_number]" class="lab-input" value="{{ $lot['batch_number'] }}"></td>
                                <td><input name="lots[{{ $index }}][expires_on]" type="date" class="lab-input" value="{{ $lot['expires_on'] }}"></td>
                                <td class="text-end font-semibold tabular-nums text-slate-700 dark:text-slate-200" data-lot-total>0.00</td>
                                <td>
                                    <button type="button" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-300" data-lot-remove aria-label="{{ __('Delete') }}">
                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.1 48.1 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.1 48.1 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.96 51.96 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.67 48.67 0 0 0-7.5 0" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('lots')<p class="px-5 py-3 text-sm text-red-700">{{ $message }}</p>@enderror
            @error('lots.0.name')<p class="px-5 py-3 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="lab-card px-5 py-4">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Items') }}</p>
                <p id="inventory-total-lots" class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">0</p>
            </div>
            <div class="lab-card px-5 py-4">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Total quantity') }}</p>
                <p id="inventory-total-qty" class="mt-1 text-2xl font-semibold tabular-nums text-teal-800 dark:text-teal-300">0.00</p>
            </div>
            <div class="lab-card px-5 py-4">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Total value') }}</p>
                <p id="inventory-total-value" class="mt-1 text-2xl font-semibold tabular-nums text-sky-800 dark:text-sky-300">0.00</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <x-btn type="submit" icon="save">{{ __('Save') }}</x-btn>
            <x-btn :href="route('inventory-items.create')" variant="secondary" icon="plus">{{ __('New entry') }}</x-btn>
            <x-btn :href="route('inventory-items.index')" variant="ghost" icon="back">{{ __('Back') }}</x-btn>
        </div>
    </form>

    <datalist id="inventory-item-name-list">
        @foreach ($itemNames as $itemName)
            <option value="{{ $itemName }}"></option>
        @endforeach
    </datalist>

    <template id="inventory-lot-template">
        <tr data-lot-row>
            <td class="lab-sheet-index text-center text-xs font-semibold text-slate-400">1</td>
            <td>
                <div class="relative min-w-40" data-lot-name-box>
                    <input name="lots[0][name]" class="lab-input pe-11" data-lot-name list="inventory-item-name-list" placeholder="{{ __('Item name') }}" autocomplete="off">
                    <button type="button" class="absolute inset-y-0 end-0 flex w-11 items-center justify-center text-slate-400 hover:text-teal-700 dark:hover:text-teal-300" data-lot-name-toggle aria-label="{{ __('Open item list') }}">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                    </button>
                    <div class="absolute z-20 mt-2 hidden max-h-64 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg dark:border-slate-700 dark:bg-slate-900" data-lot-name-menu></div>
                </div>
            </td>
            <td><input name="lots[0][quantity]" type="number" step="0.01" min="0" class="lab-input" data-lot-qty></td>
            <td><input name="lots[0][min_quantity]" type="number" step="0.01" min="0" class="lab-input" value="0"></td>
            <td><input name="lots[0][unit_cost]" type="number" step="0.01" min="0" class="lab-input" value="0" data-lot-cost></td>
            <td><input name="lots[0][batch_number]" class="lab-input"></td>
            <td><input name="lots[0][expires_on]" type="date" class="lab-input"></td>
            <td class="text-end font-semibold tabular-nums text-slate-700 dark:text-slate-200" data-lot-total>0.00</td>
            <td>
                <button type="button" class="inline-flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-700 dark:hover:bg-rose-950/40 dark:hover:text-rose-300" data-lot-remove aria-label="{{ __('Delete') }}">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.1 48.1 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.1 48.1 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.96 51.96 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.67 48.67 0 0 0-7.5 0" /></svg>
                </button>
            </td>
        </tr>
    </template>

    @push('scripts')
        <script>
            const body = document.getElementById('inventory-lot-body');
            const addRow = document.getElementById('inventory-add-row');
            const rowTemplate = document.getElementById('inventory-lot-template');
            const categoryInput = document.getElementById('inventory-category');
            const categoryBox = document.getElementById('inventory-category-combobox');
            const categoryToggle = document.getElementById('inventory-category-toggle');
            const categoryDropdown = document.getElementById('inventory-category-dropdown');
            const categories = @js($categories);
            const itemNames = @js($itemNames);
            const emptyNameHint = @js(__('Type a new item name.'));
            const emptyCategoryHint = @js(__('Type a new category.'));

            function money(value) {
                return (Number.isFinite(value) ? value : 0).toFixed(2);
            }

            function rowCount() {
                return body?.querySelectorAll('[data-lot-row]').length ?? 0;
            }

            function reindex() {
                body?.querySelectorAll('[data-lot-row]').forEach((row, index) => {
                    const marker = row.querySelector('.lab-sheet-index');
                    if (marker) {
                        marker.textContent = String(index + 1);
                    }
                    row.querySelectorAll('input[name]').forEach((input) => {
                        input.name = input.name.replace(/lots\[\d+]/, 'lots[' + index + ']');
                    });
                });
            }

            function totals() {
                let lots = 0;
                let qty = 0;
                let value = 0;

                body?.querySelectorAll('[data-lot-row]').forEach((row) => {
                    const quantity = Number(row.querySelector('[data-lot-qty]')?.value || 0);
                    const cost = Number(row.querySelector('[data-lot-cost]')?.value || 0);
                    const line = quantity * cost;
                    const cell = row.querySelector('[data-lot-total]');
                    if (cell) {
                        cell.textContent = money(line);
                    }
                    if (quantity > 0 || (row.querySelector('[data-lot-name]')?.value || '').trim() !== '') {
                        lots += 1;
                        qty += quantity;
                        value += line;
                    }
                });

                const lotsEl = document.getElementById('inventory-total-lots');
                const qtyEl = document.getElementById('inventory-total-qty');
                const valueEl = document.getElementById('inventory-total-value');
                if (lotsEl) {
                    lotsEl.textContent = String(lots);
                }
                if (qtyEl) {
                    qtyEl.textContent = money(qty);
                }
                if (valueEl) {
                    valueEl.textContent = money(value);
                }
            }

            function emptyRow() {
                const row = rowTemplate.content.firstElementChild.cloneNode(true);
                body.appendChild(row);
                reindex();
                return row;
            }

            function filteredList(values, query) {
                const needle = query.trim().toLowerCase();
                return values.filter((value) => needle === '' || String(value).toLowerCase().includes(needle));
            }

            function fillMenu(menu, values, query, hint, onPick) {
                const matches = filteredList(values, query);
                menu.innerHTML = '';
                if (matches.length === 0) {
                    const empty = document.createElement('p');
                    empty.className = 'px-3 py-4 text-center text-sm text-slate-500';
                    empty.textContent = hint;
                    menu.appendChild(empty);
                    return;
                }
                matches.forEach((value) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'block w-full rounded-lg px-3 py-2 text-start text-sm text-slate-700 hover:bg-sky-50 hover:text-sky-800 dark:text-slate-200 dark:hover:bg-slate-800';
                    button.textContent = value;
                    button.addEventListener('click', () => onPick(value));
                    menu.appendChild(button);
                });
            }

            function closeCategories() {
                categoryDropdown?.classList.add('hidden');
                categoryInput?.setAttribute('aria-expanded', 'false');
            }

            function openCategories() {
                if (!categoryDropdown || !categoryInput) {
                    return;
                }
                fillMenu(categoryDropdown, categories, categoryInput.value, emptyCategoryHint, (value) => {
                    categoryInput.value = value;
                    closeCategories();
                    categoryInput.focus();
                });
                categoryDropdown.classList.remove('hidden');
                categoryInput.setAttribute('aria-expanded', 'true');
            }

            function closeNameMenus(except) {
                body?.querySelectorAll('[data-lot-name-menu]').forEach((menu) => {
                    if (menu !== except) {
                        menu.classList.add('hidden');
                    }
                });
            }

            function openNameMenu(box) {
                const input = box.querySelector('[data-lot-name]');
                const menu = box.querySelector('[data-lot-name-menu]');
                if (!input || !menu) {
                    return;
                }
                closeNameMenus(menu);
                fillMenu(menu, itemNames, input.value, emptyNameHint, (value) => {
                    input.value = value;
                    menu.classList.add('hidden');
                    totals();
                    input.focus();
                });
                menu.classList.remove('hidden');
            }

            addRow?.addEventListener('click', (event) => {
                event.preventDefault();
                const row = emptyRow();
                totals();
                row.querySelector('[data-lot-name]')?.focus();
            });

            body?.addEventListener('input', (event) => {
                totals();
                const box = event.target.closest('[data-lot-name-box]');
                if (box && event.target.matches('[data-lot-name]')) {
                    openNameMenu(box);
                }
            });
            body?.addEventListener('focusin', (event) => {
                const box = event.target.closest('[data-lot-name-box]');
                if (box && event.target.matches('[data-lot-name]')) {
                    openNameMenu(box);
                }
            });
            body?.addEventListener('click', (event) => {
                const nameToggle = event.target.closest('[data-lot-name-toggle]');
                if (nameToggle) {
                    event.preventDefault();
                    const box = nameToggle.closest('[data-lot-name-box]');
                    const menu = box?.querySelector('[data-lot-name-menu]');
                    if (menu?.classList.contains('hidden')) {
                        openNameMenu(box);
                    } else {
                        menu?.classList.add('hidden');
                    }
                    return;
                }
                const remove = event.target.closest('[data-lot-remove]');
                if (!remove) {
                    return;
                }
                const row = remove.closest('[data-lot-row]');
                if (rowCount() <= 1) {
                    row?.querySelectorAll('input').forEach((input) => {
                        input.value = input.name.includes('[min_quantity]') || input.name.includes('[unit_cost]') ? '0' : '';
                    });
                    totals();
                    return;
                }
                row?.remove();
                reindex();
                totals();
            });

            categoryInput?.addEventListener('focus', openCategories);
            categoryInput?.addEventListener('input', openCategories);
            categoryToggle?.addEventListener('click', (event) => {
                event.preventDefault();
                if (categoryDropdown?.classList.contains('hidden')) {
                    openCategories();
                    categoryInput?.focus();
                } else {
                    closeCategories();
                }
            });
            document.addEventListener('click', (event) => {
                if (categoryBox && !categoryBox.contains(event.target)) {
                    closeCategories();
                }
                if (!(event.target instanceof Element) || !event.target.closest('[data-lot-name-box]')) {
                    closeNameMenus();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeCategories();
                    closeNameMenus();
                }
            });

            totals();
        </script>
    @endpush
</x-layout>
