<x-layout :title="__('Finance')">
    <x-page-header :description="__('Cash ledger with opening and closing balances, plus PNL from visits, purchases and expenses.')">
        <x-slot:actions>
            <x-btn :href="route('dashboard')" variant="ghost" icon="back">{{ __('Dashboard') }}</x-btn>
            <x-btn :href="route('finance.export', ['period' => $period, 'account_id' => $accountId])" variant="secondary" icon="report">{{ __('Excel / CSV') }}</x-btn>
            <x-btn :href="route('purchases.index')" variant="ghost" icon="cart">{{ __('Purchases') }}</x-btn>
            <x-btn :href="route('expenses.index')" variant="ghost" icon="pay">{{ __('Expenses') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 lab-actions">
        @foreach (['today' => __('Today'), 'week' => __('Week'), 'month' => __('Month'), 'year' => __('Year')] as $key => $label)
            <x-btn :href="route('finance.index', ['period' => $key, 'account_id' => $accountId])" size="sm" :variant="$period === $key ? 'primary' : 'slate'">{{ $label }}</x-btn>
        @endforeach
    </div>

    <h2 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">{{ __('Cash book') }}</h2>
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="__('Opening')" :value="number_format($opening, 2)" tone="sky" />
        <x-stat-card :label="__('Cash in')" :value="number_format($cashIn, 2)" tone="teal" />
        <x-stat-card :label="__('Cash out')" :value="number_format($cashOut, 2)" tone="amber" />
        <x-stat-card :label="__('Closing')" :value="number_format($closing, 2)" tone="emerald" />
    </div>

    <div class="mb-8 grid gap-6 lg:grid-cols-3">
        <x-panel class="lg:col-span-2" :title="__('New transaction')">
            <form method="POST" action="{{ route('cash-transactions.store') }}" class="grid gap-4 p-5 sm:grid-cols-2">
                @csrf
                <input type="hidden" name="period" value="{{ $period }}">
                <div>
                    <label for="account_id" class="mb-1 block text-sm">{{ __('Account') }}</label>
                    <select id="account_id" name="account_id" required class="lab-input">
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" @selected((string) old('account_id', $accountId ?: $accounts->firstWhere('is_default', true)?->id) === (string) $account->id)>{{ $account->name }}</option>
                        @endforeach
                    </select>
                    @error('account_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="type" class="mb-1 block text-sm">{{ __('Type') }}</label>
                    <select id="type" name="type" required class="lab-input">
                        <option value="in" @selected(old('type') === 'in')>{{ __('Cash in') }}</option>
                        <option value="out" @selected(old('type') === 'out')>{{ __('Cash out') }}</option>
                    </select>
                </div>
                <div>
                    <label for="amount" class="mb-1 block text-sm">{{ __('Amount') }}</label>
                    <input id="amount" name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}" required class="lab-input">
                    @error('amount')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="mb-1 block text-sm">{{ __('Description') }}</label>
                    <input id="description" name="description" value="{{ old('description') }}" class="lab-input">
                </div>
                <div class="sm:col-span-2">
                    <x-btn type="submit" icon="save">{{ __('Save') }}</x-btn>
                </div>
            </form>
        </x-panel>
        <x-panel :title="__('Accounts')">
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($accounts as $account)
                    <li class="flex justify-between px-5 py-3 text-sm">
                        <span>{{ $account->name }}</span>
                        @if ($account->is_default)
                            <x-badge tone="teal">{{ __('Default') }}</x-badge>
                        @endif
                    </li>
                @endforeach
            </ul>
            <form method="POST" action="{{ route('accounts.store') }}" class="flex gap-2 border-t border-slate-100 p-4 dark:border-slate-800">
                @csrf
                <input name="name" placeholder="{{ __('Account name') }}" required class="lab-input">
                <x-btn type="submit" variant="secondary" icon="plus">{{ __('Add') }}</x-btn>
            </form>
        </x-panel>
    </div>

    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="hidden" name="period" value="{{ $period }}">
        <select name="account_id" class="lab-input w-52" onchange="this.form.submit()">
            <option value="">{{ __('All accounts') }}</option>
            @foreach ($accounts as $account)
                <option value="{{ $account->id }}" @selected((string) $accountId === (string) $account->id)>{{ $account->name }}</option>
            @endforeach
        </select>
    </form>

    <x-panel class="mb-8" :title="__('Transactions')">
        @if ($transactions->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No transactions in this period.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                        <tr>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Date') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Account') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Description') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Amount') }}</th>
                            <th class="px-5 py-3 text-start font-medium">{{ __('Type') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($transactions as $transaction)
                            <tr>
                                <td class="px-5 py-3">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-5 py-3">{{ $transaction->account->name }}</td>
                                <td class="px-5 py-3">
                                    @if ($url = $transaction->sourceUrl())
                                        <a href="{{ $url }}" class="text-teal-800 hover:underline dark:text-teal-300">{{ $transaction->description ?: '—' }}</a>
                                        @if ($transaction->visit?->patient)
                                            <p class="text-xs text-slate-500">{{ $transaction->visit->patient->name }}</p>
                                        @endif
                                    @else
                                        {{ $transaction->description ?: '—' }}
                                    @endif
                                </td>
                                <td class="px-5 py-3 font-semibold">{{ number_format((float) $transaction->amount, 2) }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :tone="$transaction->type === \App\Enums\CashFlow::In ? 'teal' : 'amber'">{{ $transaction->type->ledgerLabel() }}</x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3 dark:border-slate-800">{{ $transactions->links() }}</div>
        @endif
    </x-panel>

    <h2 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">{{ __('Profit and loss') }}</h2>
    <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card :label="__('Patient visits')" :value="$visitCount" :href="route('reception.index')" tone="sky" />
        <x-stat-card :label="__('Lab income')" :value="number_format($income, 2)" :href="route('dashboard')" tone="teal" />
        <x-stat-card :label="__('Purchases')" :value="number_format($purchases, 2)" :href="route('purchases.index')" tone="amber" />
        <x-stat-card :label="__('Net profit')" :value="number_format($profit, 2)" tone="emerald" />
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-panel :title="__('Daily')">
            <div class="px-5 py-4">
                <canvas id="cash-daily" class="h-56 w-full" height="224"></canvas>
            </div>
        </x-panel>
        <x-panel :title="__('Summary')">
            <dl class="divide-y divide-slate-100 dark:divide-slate-800">
                <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Unique patients') }}</dt><dd class="font-semibold">{{ $uniquePatients }}</dd></div>
                <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Tests performed') }}</dt><dd class="font-semibold">{{ $testsPerformed }}</dd></div>
                <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Other expenses') }}</dt><dd class="font-semibold"><a href="{{ route('expenses.index') }}" class="hover:underline">{{ number_format($expenses, 2) }}</a></dd></div>
                <div class="flex justify-between px-5 py-3 text-sm"><dt>{{ __('Period') }}</dt><dd>{{ $from->toDateString() }} — {{ $to->toDateString() }}</dd></div>
            </dl>
        </x-panel>
        <x-panel :title="__('Top tests')">
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($topTests as $row)
                    <li class="flex justify-between px-5 py-3 text-sm"><span>{{ $row->name }}</span><span class="font-semibold">{{ $row->total }}</span></li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No tests have been assigned.') }}</li>
                @endforelse
            </ul>
        </x-panel>
        <x-panel :title="__('Referring doctors')">
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($topDoctors as $row)
                    <li class="flex justify-between px-5 py-3 text-sm"><span>{{ $row->name }}</span><span class="font-semibold">{{ $row->total }}</span></li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-500">{{ __('No doctor has been registered.') }}</li>
                @endforelse
            </ul>
        </x-panel>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        const isDark = document.documentElement.classList.contains('dark');
        const tick = isDark ? '#94a3b8' : '#64748b';
        const grid = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(148, 163, 184, 0.18)';
        const daily = {{ Illuminate\Support\Js::from($daily) }};
        Chart.defaults.color = tick;
        const canvas = document.getElementById('cash-daily');
        if (canvas) {
            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: daily.map((row) => row.label),
                    datasets: [
                        { label: @json(__('Cash in')), data: daily.map((row) => row.in), backgroundColor: '#0f766e', borderRadius: 6 },
                        { label: @json(__('Cash out')), data: daily.map((row) => row.out), backgroundColor: '#d97706', borderRadius: 6 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { color: grid } },
                    },
                },
            });
        }
    </script>
    @endpush
</x-layout>
