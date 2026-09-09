<?php

namespace App\Http\Controllers;

use App\Enums\CashFlow;
use App\Models\Account;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\PatientTest;
use App\Models\Purchase;
use App\Models\Visit;
use App\Support\CashLedger;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    public function index(Request $request, CashLedger $ledger): View
    {
        [$from, $to, $period] = $this->periodBounds($request);
        $accountId = $request->integer('account_id') ?: null;

        $transactions = CashTransaction::query()
            ->with(['account', 'visit.patient', 'expense', 'purchase'])
            ->when($accountId, fn ($query) => $query->where('account_id', $accountId))
            ->whereBetween('created_at', [$from, $to])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $opening = $this->balanceBefore($from, $accountId);
        [$cashIn, $cashOut] = $this->cashTotals($from, $to, $accountId);

        return view('finance.index', [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'accountId' => $accountId,
            'accounts' => Account::query()->orderBy('name')->get(['id', 'name']),
            'transactions' => $transactions,
            'opening' => $opening,
            'cashIn' => $cashIn,
            'cashOut' => $cashOut,
            'closing' => $opening + $cashIn - $cashOut,
            'daily' => $this->dailySeries($from, $to, $accountId),
            ...$this->summary($from, $to, $ledger),
        ]);
    }

    public function export(Request $request, CashLedger $ledger): StreamedResponse
    {
        [$from, $to, $period] = $this->periodBounds($request);
        $summary = $this->summary($from, $to, $ledger);
        $accountId = $request->integer('account_id') ?: null;
        $opening = $this->balanceBefore($from, $accountId);
        [$cashIn, $cashOut] = $this->cashTotals($from, $to, $accountId);

        $filename = 'ssml-finance-'.$period.'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($summary, $from, $to, $period, $opening, $cashIn, $cashOut): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [__('Sadat Salihi Medical Laboratory'), $period, $from->toDateString(), $to->toDateString()]);
            fputcsv($handle, [__('Item'), __('Amount')]);
            fputcsv($handle, [__('Opening'), number_format($opening, 2, '.', '')]);
            fputcsv($handle, [__('Cash in'), number_format($cashIn, 2, '.', '')]);
            fputcsv($handle, [__('Cash out'), number_format($cashOut, 2, '.', '')]);
            fputcsv($handle, [__('Closing'), number_format($opening + $cashIn - $cashOut, 2, '.', '')]);
            fputcsv($handle, [__('Patient visits'), $summary['visitCount']]);
            fputcsv($handle, [__('Unique patients'), $summary['uniquePatients']]);
            fputcsv($handle, [__('Tests performed'), $summary['testsPerformed']]);
            fputcsv($handle, [__('Lab income'), $summary['income']]);
            fputcsv($handle, [__('Purchases'), $summary['purchases']]);
            fputcsv($handle, [__('Other expenses'), $summary['expenses']]);
            fputcsv($handle, [__('Net profit'), $summary['profit']]);
            fputcsv($handle, []);
            fputcsv($handle, [__('Top tests'), __('Count')]);
            foreach ($summary['topTests'] as $row) {
                fputcsv($handle, [$row->name, $row->total]);
            }
            fputcsv($handle, []);
            fputcsv($handle, [__('Referring doctors'), __('Patients')]);
            foreach ($summary['topDoctors'] as $row) {
                fputcsv($handle, [$row->name, $row->total]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface, 2: string}
     */
    private function periodBounds(Request $request): array
    {
        $period = $request->string('period')->toString();

        if (! in_array($period, ['today', 'week', 'month', 'year'], true)) {
            $period = 'month';
        }

        $from = match ($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        return [$from, now()->endOfDay(), $period];
    }

    private function balanceBefore(CarbonInterface $from, ?int $accountId): float
    {
        $query = CashTransaction::query()
            ->when($accountId, fn ($builder) => $builder->where('account_id', $accountId))
            ->where('created_at', '<', $from);

        $row = $query
            ->selectRaw(
                'coalesce(sum(case when type = ? then amount else 0 end), 0) as cash_in, coalesce(sum(case when type = ? then amount else 0 end), 0) as cash_out',
                [CashFlow::In->value, CashFlow::Out->value],
            )
            ->first();

        return round((float) ($row?->cash_in ?? 0) - (float) ($row?->cash_out ?? 0), 2);
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function cashTotals(CarbonInterface $from, CarbonInterface $to, ?int $accountId): array
    {
        $row = CashTransaction::query()
            ->when($accountId, fn ($query) => $query->where('account_id', $accountId))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw(
                'coalesce(sum(case when type = ? then amount else 0 end), 0) as cash_in, coalesce(sum(case when type = ? then amount else 0 end), 0) as cash_out',
                [CashFlow::In->value, CashFlow::Out->value],
            )
            ->first();

        return [(float) ($row?->cash_in ?? 0), (float) ($row?->cash_out ?? 0)];
    }

    /**
     * @return Collection<int, array{label: string, in: float, out: float}>
     */
    private function dailySeries(CarbonInterface $from, CarbonInterface $to, ?int $accountId): Collection
    {
        $rows = CashTransaction::query()
            ->selectRaw('date(created_at) as day, type, sum(amount) as total')
            ->when($accountId, fn ($query) => $query->where('account_id', $accountId))
            ->whereBetween('created_at', [$from, $to])
            ->groupByRaw('date(created_at), type')
            ->get()
            ->groupBy('day');

        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay());

        return collect(range(0, max(0, $days)))->map(function (int $offset) use ($from, $rows): array {
            $day = $from->copy()->startOfDay()->addDays($offset);
            $key = $day->toDateString();
            $ofDay = $rows->get($key, collect());

            return [
                'label' => $day->translatedFormat('d M'),
                'in' => round((float) $ofDay->firstWhere('type', CashFlow::In)?->total, 2),
                'out' => round((float) $ofDay->firstWhere('type', CashFlow::Out)?->total, 2),
            ];
        });
    }

    /**
     * @return array{
     *     visitCount: int,
     *     uniquePatients: int,
     *     testsPerformed: int,
     *     income: float,
     *     purchases: float,
     *     expenses: float,
     *     profit: float,
     *     topTests: Collection<int, object>,
     *     topDoctors: Collection<int, object>
     * }
     */
    private function summary(CarbonInterface $from, CarbonInterface $to, CashLedger $ledger): array
    {
        $paidVisits = Visit::query()
            ->where('paid', true)
            ->whereBetween('updated_at', [$from, $to]);

        $income = $ledger->labIncome($from, $to);
        $purchases = (float) Purchase::query()->whereBetween('billed_on', [$from->toDateString(), $to->toDateString()])->sum('subtotal');
        $expenses = (float) Expense::query()->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])->sum('amount');

        $topTests = PatientTest::query()
            ->selectRaw('tests.name as name, count(*) as total')
            ->join('tests', 'tests.id', '=', 'patient_tests.test_id')
            ->where('patient_tests.paid', true)
            ->whereBetween('patient_tests.updated_at', [$from, $to])
            ->groupBy('tests.id', 'tests.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $topDoctors = Visit::query()
            ->selectRaw('coalesce(doctors.name, ?) as name, count(*) as total', [__('Self request')])
            ->leftJoin('doctors', 'doctors.id', '=', 'visits.doctor_id')
            ->whereBetween('visits.created_at', [$from, $to])
            ->groupByRaw('coalesce(doctors.name, ?)', [__('Self request')])
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'visitCount' => (clone $paidVisits)->count(),
            'uniquePatients' => (clone $paidVisits)->distinct()->count('patient_id'),
            'testsPerformed' => PatientTest::query()
                ->where('paid', true)
                ->whereBetween('updated_at', [$from, $to])
                ->count(),
            'income' => $income,
            'purchases' => $purchases,
            'expenses' => $expenses,
            'profit' => $income - $purchases - $expenses,
            'topTests' => $topTests,
            'topDoctors' => $topDoctors,
        ];
    }
}
