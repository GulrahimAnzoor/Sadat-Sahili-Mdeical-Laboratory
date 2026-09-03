<?php

namespace App\Http\Controllers;

use App\Enums\CashFlow;
use App\Enums\TestDepartment;
use App\Models\CashTransaction;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\Visit;
use App\Support\CashLedger;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(CashLedger $ledger): View
    {
        $from = now()->subDays(13)->startOfDay();

        $recentVisits = Visit::query()
            ->where('created_at', '>=', $from)
            ->get(['created_at']);

        $incomeByDay = CashTransaction::query()
            ->selectRaw('date(created_at) as day, sum(amount) as total')
            ->where('type', CashFlow::In)
            ->whereNotNull('visit_id')
            ->where('created_at', '>=', $from)
            ->groupByRaw('date(created_at)')
            ->pluck('total', 'day');

        $trend = collect(range(13, 0))->map(function (int $daysAgo) use ($recentVisits, $incomeByDay): array {
            $day = now()->subDays($daysAgo);
            $key = $day->toDateString();

            return [
                'label' => $day->translatedFormat('d M'),
                'visits' => $recentVisits->filter(
                    fn (Visit $visit): bool => $visit->created_at->isSameDay($day),
                )->count(),
                'income' => round((float) $incomeByDay->get($key, 0), 2),
            ];
        });

        $topTests = PatientTest::query()
            ->selectRaw('tests.name as name, count(*) as total')
            ->join('tests', 'tests.id', '=', 'patient_tests.test_id')
            ->groupBy('tests.id', 'tests.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $departmentMix = PatientTest::query()
            ->selectRaw('tests.department as department, count(*) as total')
            ->join('tests', 'tests.id', '=', 'patient_tests.test_id')
            ->groupBy('tests.department')
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $row): array => [
                'label' => TestDepartment::tryFrom((string) $row->department)?->label() ?? (string) $row->department,
                'total' => (int) $row->total,
            ]);

        $unpaidVisits = Visit::unpaid()
            ->whereHas('patientTests')
            ->with(['patient', 'patientTests.test'])
            ->latest('id')
            ->limit(3)
            ->get();

        $awaitingResults = Visit::awaitingResult()
            ->with(['patient', 'patientTests.test'])
            ->latest('id')
            ->limit(3)
            ->get();

        return view('dashboard', [
            'patientCount' => Patient::query()->count(),
            'todayPatientCount' => Patient::query()->whereDate('created_at', today())->count(),
            'unpaidCount' => Visit::unpaid()->whereHas('patientTests')->count(),
            'unpaidAmount' => (float) Visit::unpaid()
                ->whereHas('patientTests')
                ->selectRaw('coalesce(sum(total - paid_amount), 0) as remaining')
                ->value('remaining'),
            'paidAmount' => $ledger->labIncome(),
            'todayIncome' => $ledger->labIncome(now()->startOfDay(), now()->endOfDay()),
            'completedVisitCount' => Visit::query()
                ->whereHas('patientTests')
                ->whereHas('testResults')
                ->whereDoesntHave('patientTests', fn ($query) => $query->awaitingResult())
                ->count(),
            'awaitingResultCount' => Visit::awaitingResult()->count(),
            'trendLabels' => $trend->pluck('label'),
            'trendVisits' => $trend->pluck('visits'),
            'trendIncome' => $trend->pluck('income'),
            'topTestLabels' => $topTests->pluck('name'),
            'topTestCounts' => $topTests->pluck('total')->map(fn (mixed $total): int => (int) $total),
            'departmentLabels' => $departmentMix->pluck('label'),
            'departmentCounts' => $departmentMix->pluck('total'),
            'unpaidVisits' => $unpaidVisits,
            'awaitingResults' => $awaitingResults,
        ]);
    }
}
