<x-layout :title="__('Dashboard')">
    <section class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-teal-800 via-sky-700 to-cyan-600 px-6 py-6 text-white shadow-lg shadow-teal-900/20 sm:px-8">
        <div class="pointer-events-none absolute -top-16 -right-10 size-48 rounded-full bg-white/10 rtl:-left-10 rtl:right-auto"></div>
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm text-teal-100">{{ __('Sadat Salihi Medical Laboratory') }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('Welcome') }}</h2>
                <p class="mt-1 text-sm text-teal-50">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('lab.manage')
                    <x-btn :href="route('worklist')" variant="white" icon="flask">{{ __('Enter results') }}</x-btn>
                @endcan
                @can('reports.view')
                    <x-btn :href="route('reports.index')" variant="glass" icon="report">{{ __('Reports') }}</x-btn>
                @endcan
                @can('tests.manage')
                    <x-btn :href="route('tests.index')" variant="glass" icon="list">{{ __('Catalogue') }}</x-btn>
                @endcan
            </div>
        </div>
    </section>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @can('patients.manage')
            <x-stat-card :label="__('Patients')" :value="$patientCount" :href="route('patients.index')" :hint="__('New today: :count', ['count' => $todayPatientCount])" tone="teal">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19a4 4 0 0 0-8 0m8-8a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
            </x-stat-card>
        @endcan
        @can('reception.manage')
            <x-stat-card :label="__('Unpaid')" :value="$unpaidCount" :href="route('reception.index')" :hint="__('Outstanding :amount', ['amount' => number_format((float) $unpaidAmount, 2)])" tone="amber">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m4-14H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H8" /></svg>
            </x-stat-card>
        @endcan
        @can('lab.manage')
            <x-stat-card :label="__('Pending results')" :value="$awaitingResultCount" :href="route('worklist')" :hint="__('Assigned, no result yet')" tone="rose">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5l3 2m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </x-stat-card>
        @endcan
        @can('finance.manage')
            <x-stat-card :label="__('Lab income')" :value="number_format((float) $todayIncome, 2)" :href="route('finance.index', ['period' => 'today'])" :hint="__('Paid :amount', ['amount' => number_format((float) $paidAmount, 2)])" tone="sky">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m4 14V9m4 10V7m4 12v-6m4 6V4" /></svg>
            </x-stat-card>
        @endcan
    </div>

    <div class="mb-6 grid gap-6 xl:grid-cols-3">
        <x-panel class="xl:col-span-2" :title="__('Visits')">
            <x-slot:subtitle>{{ __('Last 14 days') }}</x-slot:subtitle>
            <div class="px-5 py-4">
                <canvas id="visits-trend" class="h-64 w-full" height="256"></canvas>
            </div>
        </x-panel>

        <x-panel :title="__('Workflow')">
            <x-slot:subtitle>{{ __('Current laboratory load') }}</x-slot:subtitle>
            <div class="flex items-center justify-center px-5 py-4">
                <canvas id="workflow-chart" class="max-h-64" height="256"></canvas>
            </div>
        </x-panel>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <x-panel :title="__('Top tests')">
            <x-slot:subtitle>{{ __('Most requested examinations') }}</x-slot:subtitle>
            <x-slot:action>
                @can('tests.manage')
                    <x-btn :href="route('tests.index')" size="sm" variant="teal">{{ __('Catalogue') }}</x-btn>
                @endcan
            </x-slot:action>
            <div class="px-5 py-4">
                <canvas id="top-tests-chart" class="h-56 w-full" height="224"></canvas>
            </div>
        </x-panel>

        <x-panel :title="__('Departments')">
            <x-slot:subtitle>{{ __('Share of assigned tests') }}</x-slot:subtitle>
            <div class="px-5 py-4">
                <canvas id="departments-chart" class="h-56 w-full" height="224"></canvas>
            </div>
        </x-panel>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-panel :title="__('Unpaid')">
            <x-slot:action>
                @can('reception.manage')
                    <x-btn :href="route('reception.index')" size="sm" variant="teal">{{ __('Reception') }}</x-btn>
                @endcan
            </x-slot:action>
            @if ($unpaidVisits->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('All payments have been received.') }}</p>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($unpaidVisits as $unpaidVisit)
                        <li class="flex items-center justify-between gap-3 px-5 py-3 transition duration-150 hover:bg-slate-50/90 dark:hover:bg-slate-800/40">
                            <a href="{{ route('patients.show', $unpaidVisit->patient) }}" class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $unpaidVisit->patient->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $unpaidVisit->token_code }} · {{ $unpaidVisit->patientTests->pluck('test.name')->filter()->join(', ') }}</p>
                            </a>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="text-sm font-semibold text-amber-700 dark:text-amber-300">{{ number_format($unpaidVisit->remainingAmount(), 2) }}</span>
                                <form method="POST" action="{{ route('visits.payment', $unpaidVisit) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-btn type="submit" size="sm" variant="amber" icon="pay">{{ __('Pay') }}</x-btn>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-panel>

        <x-panel :title="__('Pending results')">
            <x-slot:action>
                @can('lab.manage')
                    <x-btn :href="route('worklist')" size="sm" variant="amber">{{ __('Lab') }}</x-btn>
                @endcan
            </x-slot:action>
            @if ($awaitingResults->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ __('All results have been recorded.') }}</p>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($awaitingResults as $awaitingVisit)
                        <li class="flex items-center justify-between gap-3 px-5 py-3 transition duration-150 hover:bg-slate-50/90 dark:hover:bg-slate-800/40">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900 dark:text-white">{{ $awaitingVisit->patient->name }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $awaitingVisit->token_code }} · {{ $awaitingVisit->patientTests->pluck('test.name')->filter()->join(', ') }}</p>
                            </div>
                            <x-btn :href="route('visits.results.edit', $awaitingVisit)" size="sm" variant="teal" icon="flask">{{ __('Result') }}</x-btn>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-panel>
    </div>

    @push('scripts')
    <script src="{{ asset('vendor/chart.umd.min.js') }}"></script>
    <script>
        const isDark = document.documentElement.classList.contains('dark');
        const isRtl = document.documentElement.dir === 'rtl';
        const tick = isDark ? '#94a3b8' : '#64748b';
        const grid = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(148, 163, 184, 0.18)';
        const trendLabels = {{ Illuminate\Support\Js::from($trendLabels) }};
        const trendVisits = {{ Illuminate\Support\Js::from($trendVisits) }};
        const trendIncome = {{ Illuminate\Support\Js::from($trendIncome) }};
        const topTestLabels = {{ Illuminate\Support\Js::from($topTestLabels) }};
        const topTestCounts = {{ Illuminate\Support\Js::from($topTestCounts) }};
        const departmentLabels = {{ Illuminate\Support\Js::from($departmentLabels) }};
        const departmentCounts = {{ Illuminate\Support\Js::from($departmentCounts) }};

        Chart.defaults.color = tick;
        Chart.defaults.borderColor = grid;
        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;

        const visitsCanvas = document.getElementById('visits-trend');
        if (visitsCanvas) {
            new Chart(visitsCanvas, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: @json(__('Visits')),
                            data: trendVisits,
                            borderColor: '#0f766e',
                            backgroundColor: 'rgba(13, 148, 136, 0.16)',
                            fill: true,
                            tension: 0.35,
                            yAxisID: 'y',
                        },
                        {
                            label: @json(__('Lab income')),
                            data: trendIncome,
                            borderColor: '#0369a1',
                            backgroundColor: 'transparent',
                            tension: 0.35,
                            yAxisID: 'y1',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'bottom', rtl: isRtl, labels: { boxWidth: 10, padding: 16 } } },
                    scales: {
                        x: { grid: { display: false }, reverse: isRtl },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } },
                        y1: { beginAtZero: true, position: isRtl ? 'left' : 'right', grid: { drawOnChartArea: false } },
                    },
                },
            });
        }

        const workflowCanvas = document.getElementById('workflow-chart');
        if (workflowCanvas) {
            new Chart(workflowCanvas, {
                type: 'doughnut',
                data: {
                    labels: [@json(__('Unpaid')), @json(__('Pending results')), @json(__('Completed'))],
                    datasets: [{
                        data: [{{ (int) $unpaidCount }}, {{ (int) $awaitingResultCount }}, {{ (int) $completedVisitCount }}],
                        backgroundColor: ['#f59e0b', '#f43f5e', '#0d9488'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: { legend: { position: 'bottom', rtl: isRtl, labels: { boxWidth: 10, padding: 14 } } },
                },
            });
        }

        const topCanvas = document.getElementById('top-tests-chart');
        if (topCanvas) {
            new Chart(topCanvas, {
                type: 'bar',
                data: {
                    labels: topTestLabels.length ? topTestLabels : [@json(__('No tests have been assigned.'))],
                    datasets: [{
                        label: @json(__('Tests')),
                        data: topTestCounts.length ? topTestCounts : [0],
                        backgroundColor: '#0f766e',
                        borderRadius: 8,
                        barPercentage: 0.55,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } },
                        y: { grid: { display: false }, reverse: false },
                    },
                },
            });
        }

        const deptCanvas = document.getElementById('departments-chart');
        if (deptCanvas) {
            new Chart(deptCanvas, {
                type: 'bar',
                data: {
                    labels: departmentLabels.length ? departmentLabels : [@json(__('Tests'))],
                    datasets: [{
                        label: @json(__('Tests')),
                        data: departmentCounts.length ? departmentCounts : [0],
                        backgroundColor: ['#0f766e', '#0369a1', '#7c3aed', '#be123c', '#d97706'],
                        borderRadius: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } },
                    },
                },
            });
        }
    </script>
    @endpush
</x-layout>
