<x-layout :title="__('Period report')" print="a4">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ __('Period report') }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $from->toDateString() }} — {{ $to->toDateString() }}</p>
        </div>
        <div class="lab-actions">
            <x-btn type="button" onclick="window.print()" icon="print">{{ __('Print') }}</x-btn>
            <x-btn :href="route('finance.index', ['period' => $period])" variant="ghost" icon="back">{{ __('Finance') }}</x-btn>
        </div>
    </div>

    <div class="mb-6 lab-actions print:hidden">
        @foreach (['today' => __('Today'), 'week' => __('Week'), 'month' => __('Month')] as $key => $label)
            <x-btn :href="route('finance.period-report', ['period' => $key])" size="sm" :variant="$period === $key ? 'primary' : 'slate'">{{ $label }}</x-btn>
        @endforeach
    </div>

    <x-panel class="mb-8 print:hidden" :title="__('Categories to print')">
        <div class="flex flex-wrap gap-4 px-5 py-4 text-sm">
            @foreach ([
                'patients' => __('Patients'),
                'consumers' => __('Consumers'),
                'consumed' => __('Consumed items'),
                'purchases' => __('Purchases'),
                'expenses' => __('Expenses'),
                'cash_in' => __('Cash received'),
                'cash_out' => __('Cash paid out'),
            ] as $key => $label)
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" value="{{ $key }}" data-section-toggle checked class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>
    </x-panel>

    <header class="mb-6 hidden print:block">
        <p class="text-sm font-semibold">{{ __('Sadat Salihi Medical Laboratory') }}</p>
        <h1 class="text-xl font-semibold">{{ __('Period report') }}</h1>
        <p class="text-sm">{{ $from->toDateString() }} — {{ $to->toDateString() }}</p>
    </header>

    <div class="space-y-8">
        <section data-section="patients">
            <x-panel :title="__('Patients')">
                @if ($visits->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No patients in this period.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                                <tr>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Date') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Patient') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Doctor') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Tests') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Paid') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($visits as $visit)
                                    <tr>
                                        <td class="px-5 py-3">{{ $visit->updated_at->format('Y-m-d') }}</td>
                                        <td class="px-5 py-3">{{ $visit->patient?->name ?? '—' }}</td>
                                        <td class="px-5 py-3">{{ $visit->referrerLabel() }}</td>
                                        <td class="px-5 py-3">{{ $visit->patientTests->pluck('test.name')->filter()->join(', ') ?: '—' }}</td>
                                        <td class="px-5 py-3 font-semibold">{{ number_format((float) $visit->paid_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <dl class="divide-y divide-slate-100 border-t border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Patient visits') }}</dt><dd class="font-semibold">{{ $totals['visitCount'] }}</dd></div>
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Unique patients') }}</dt><dd class="font-semibold">{{ $totals['uniquePatients'] }}</dd></div>
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Paid') }}</dt><dd class="font-semibold">{{ number_format($totals['patientPayments'], 2) }}</dd></div>
                </dl>
            </x-panel>
        </section>

        <section data-section="consumers">
            <x-panel :title="__('Consumers')">
                @if ($consumers->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No consumers in this period.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                                <tr>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Staff') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Movements') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($consumers as $consumer)
                                    <tr>
                                        <td class="px-5 py-3">{{ $consumer['name'] }}</td>
                                        <td class="px-5 py-3">{{ $consumer['movements'] }}</td>
                                        <td class="px-5 py-3 font-semibold">{{ number_format($consumer['quantity'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <dl class="border-t border-slate-100 dark:border-slate-800">
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Consumers') }}</dt><dd class="font-semibold">{{ $totals['consumerCount'] }}</dd></div>
                </dl>
            </x-panel>
        </section>

        <section data-section="consumed">
            <x-panel :title="__('Consumed items')">
                @if ($consumedItems->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No consumed items in this period.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                                <tr>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Date') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Item') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Type') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Staff') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($consumedItems as $movement)
                                    <tr>
                                        <td class="px-5 py-3">{{ $movement->occurred_on?->toDateString() }}</td>
                                        <td class="px-5 py-3">{{ $movement->inventoryItem?->name ?? '—' }}</td>
                                        <td class="px-5 py-3">{{ $movement->type->label() }}</td>
                                        <td class="px-5 py-3">{{ $movement->user?->name ?? __('Unassigned') }}</td>
                                        <td class="px-5 py-3 font-semibold">{{ number_format((float) $movement->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <dl class="divide-y divide-slate-100 border-t border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Total quantity') }}</dt><dd class="font-semibold">{{ number_format($totals['consumedQuantity'], 2) }}</dd></div>
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Total value') }}</dt><dd class="font-semibold">{{ number_format($totals['consumedValue'], 2) }}</dd></div>
                </dl>
            </x-panel>
        </section>

        <section data-section="purchases">
            <x-panel :title="__('Purchases')">
                @if ($purchases->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No purchases in this period.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                                <tr>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Date') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Bill no.') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Supplier') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Received') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($purchases as $purchase)
                                    <tr>
                                        <td class="px-5 py-3">{{ $purchase->billed_on?->toDateString() }}</td>
                                        <td class="px-5 py-3">{{ $purchase->bill_number }}</td>
                                        <td class="px-5 py-3">{{ $purchase->supplier?->name ?? '—' }}</td>
                                        <td class="px-5 py-3">{{ number_format((float) $purchase->received, 2) }}</td>
                                        <td class="px-5 py-3 font-semibold">{{ number_format((float) $purchase->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <dl class="divide-y divide-slate-100 border-t border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Purchases') }}</dt><dd class="font-semibold">{{ $totals['purchaseCount'] }}</dd></div>
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Amount') }}</dt><dd class="font-semibold">{{ number_format($totals['purchaseTotal'], 2) }}</dd></div>
                </dl>
            </x-panel>
        </section>

        <section data-section="expenses">
            <x-panel :title="__('Expenses')">
                @if ($expenses->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No expenses in this period.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                                <tr>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Date') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Title') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Category') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($expenses as $expense)
                                    <tr>
                                        <td class="px-5 py-3">{{ $expense->spent_on?->toDateString() }}</td>
                                        <td class="px-5 py-3">{{ $expense->title }}</td>
                                        <td class="px-5 py-3">{{ $expense->category->label() }}</td>
                                        <td class="px-5 py-3 font-semibold">{{ number_format((float) $expense->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <dl class="divide-y divide-slate-100 border-t border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Expenses') }}</dt><dd class="font-semibold">{{ $totals['expenseCount'] }}</dd></div>
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Amount') }}</dt><dd class="font-semibold">{{ number_format($totals['expenseTotal'], 2) }}</dd></div>
                </dl>
            </x-panel>
        </section>

        <section data-section="cash_in">
            <x-panel :title="__('Cash received')">
                @if ($cashIn->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No cash received in this period.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                                <tr>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Date') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Account') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Source') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($cashIn as $transaction)
                                    <tr>
                                        <td class="px-5 py-3">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-5 py-3">{{ $transaction->account?->name ?? '—' }}</td>
                                        <td class="px-5 py-3">{{ $transaction->partyLabel() }}</td>
                                        <td class="px-5 py-3 font-semibold">{{ number_format((float) $transaction->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <dl class="divide-y divide-slate-100 border-t border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Cash received') }}</dt><dd class="font-semibold">{{ $totals['cashInCount'] }}</dd></div>
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Amount') }}</dt><dd class="font-semibold">{{ number_format($totals['cashInTotal'], 2) }}</dd></div>
                </dl>
            </x-panel>
        </section>

        <section data-section="cash_out">
            <x-panel :title="__('Cash paid out')">
                @if ($cashOut->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No cash paid out in this period.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                                <tr>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Date') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Account') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Source') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($cashOut as $transaction)
                                    <tr>
                                        <td class="px-5 py-3">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                        <td class="px-5 py-3">{{ $transaction->account?->name ?? '—' }}</td>
                                        <td class="px-5 py-3">{{ $transaction->partyLabel() }}</td>
                                        <td class="px-5 py-3 font-semibold">{{ number_format((float) $transaction->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                <dl class="divide-y divide-slate-100 border-t border-slate-100 dark:divide-slate-800 dark:border-slate-800">
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Cash paid out') }}</dt><dd class="font-semibold">{{ $totals['cashOutCount'] }}</dd></div>
                    <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Amount') }}</dt><dd class="font-semibold">{{ number_format($totals['cashOutTotal'], 2) }}</dd></div>
                </dl>
            </x-panel>
        </section>

        <x-panel :title="__('Grand totals')">
            <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                <div data-section="patients" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Patient visits') }}</dt><dd class="font-semibold">{{ $totals['visitCount'] }}</dd></div>
                <div data-section="patients" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Unique patients') }}</dt><dd class="font-semibold">{{ $totals['uniquePatients'] }}</dd></div>
                <div data-section="patients" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Patient payments') }}</dt><dd class="font-semibold">{{ number_format($totals['patientPayments'], 2) }}</dd></div>
                <div data-section="consumers" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Consumers') }}</dt><dd class="font-semibold">{{ $totals['consumerCount'] }}</dd></div>
                <div data-section="consumed" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Consumed items') }}</dt><dd class="font-semibold">{{ number_format($totals['consumedQuantity'], 2) }}</dd></div>
                <div data-section="consumed" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Consumed value') }}</dt><dd class="font-semibold">{{ number_format($totals['consumedValue'], 2) }}</dd></div>
                <div data-section="purchases" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Purchases') }}</dt><dd class="font-semibold">{{ number_format($totals['purchaseTotal'], 2) }}</dd></div>
                <div data-section="expenses" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Expenses') }}</dt><dd class="font-semibold">{{ number_format($totals['expenseTotal'], 2) }}</dd></div>
                <div data-section="cash_in" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Cash in') }}</dt><dd class="font-semibold">{{ number_format($totals['cashInTotal'], 2) }}</dd></div>
                <div data-section="cash_out" class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Cash out') }}</dt><dd class="font-semibold">{{ number_format($totals['cashOutTotal'], 2) }}</dd></div>
            </dl>
        </x-panel>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('[data-section-toggle]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                document.querySelectorAll(`[data-section="${checkbox.value}"]`).forEach((section) => {
                    section.classList.toggle('hidden', ! checkbox.checked);
                });
            });
        });
    </script>
    @endpush
</x-layout>
