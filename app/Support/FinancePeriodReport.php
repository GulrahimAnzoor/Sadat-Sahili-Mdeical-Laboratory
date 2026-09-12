<?php

namespace App\Support;

use App\Enums\CashFlow;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Visit;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class FinancePeriodReport
{
    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface, 2: string}
     */
    public function periodBounds(string $period): array
    {
        if (! in_array($period, ['today', 'week', 'month'], true)) {
            $period = 'today';
        }

        $from = match ($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            default => now()->startOfMonth(),
        };

        return [$from, now()->endOfDay(), $period];
    }

    /**
     * @return array{
     *     period: string,
     *     from: CarbonInterface,
     *     to: CarbonInterface,
     *     visits: Collection<int, Visit>,
     *     consumers: Collection<int, array{name: string, movements: int, quantity: float}>,
     *     consumedItems: Collection<int, StockMovement>,
     *     purchases: Collection<int, Purchase>,
     *     expenses: Collection<int, Expense>,
     *     cashIn: Collection<int, CashTransaction>,
     *     cashOut: Collection<int, CashTransaction>,
     *     totals: array{
     *         visitCount: int,
     *         uniquePatients: int,
     *         patientPayments: float,
     *         consumerCount: int,
     *         consumedQuantity: float,
     *         consumedValue: float,
     *         purchaseCount: int,
     *         purchaseTotal: float,
     *         expenseCount: int,
     *         expenseTotal: float,
     *         cashInCount: int,
     *         cashInTotal: float,
     *         cashOutCount: int,
     *         cashOutTotal: float
     *     }
     * }
     */
    public function build(string $period): array
    {
        [$from, $to, $period] = $this->periodBounds($period);

        $visits = Visit::query()
            ->with(['patient:id,name', 'doctor:id,name', 'patientTests.test:id,name'])
            ->where('paid', true)
            ->whereBetween('updated_at', [$from, $to])
            ->latest('id')
            ->get();

        $consumedItems = StockMovement::query()
            ->outbound()
            ->occurredBetween($from, $to)
            ->with(['inventoryItem:id,name,unit_cost', 'user:id,name'])
            ->latest('id')
            ->get();

        $consumers = $consumedItems
            ->groupBy(fn (StockMovement $movement): int => $movement->user_id ?? 0)
            ->map(function (Collection $rows): array {
                return [
                    'name' => $rows->first()?->user?->name ?? __('Unassigned'),
                    'movements' => $rows->count(),
                    'quantity' => round((float) $rows->sum('quantity'), 2),
                ];
            })
            ->values();

        $purchases = Purchase::query()
            ->with('supplier:id,name')
            ->whereDate('billed_on', '>=', $from)
            ->whereDate('billed_on', '<=', $to)
            ->latest('id')
            ->get();

        $expenses = Expense::query()
            ->whereDate('spent_on', '>=', $from)
            ->whereDate('spent_on', '<=', $to)
            ->latest('id')
            ->get();

        $cashIn = $this->cashTransactions($from, $to, CashFlow::In);
        $cashOut = $this->cashTransactions($from, $to, CashFlow::Out);

        $consumedValue = round($consumedItems->sum(
            fn (StockMovement $movement): float => (float) $movement->quantity * (float) ($movement->inventoryItem?->unit_cost ?? 0),
        ), 2);

        return [
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'visits' => $visits,
            'consumers' => $consumers,
            'consumedItems' => $consumedItems,
            'purchases' => $purchases,
            'expenses' => $expenses,
            'cashIn' => $cashIn,
            'cashOut' => $cashOut,
            'totals' => [
                'visitCount' => $visits->count(),
                'uniquePatients' => $visits->unique('patient_id')->count(),
                'patientPayments' => round((float) $visits->sum('paid_amount'), 2),
                'consumerCount' => $consumers->count(),
                'consumedQuantity' => round((float) $consumedItems->sum('quantity'), 2),
                'consumedValue' => $consumedValue,
                'purchaseCount' => $purchases->count(),
                'purchaseTotal' => round((float) $purchases->sum('subtotal'), 2),
                'expenseCount' => $expenses->count(),
                'expenseTotal' => round((float) $expenses->sum('amount'), 2),
                'cashInCount' => $cashIn->count(),
                'cashInTotal' => round((float) $cashIn->sum('amount'), 2),
                'cashOutCount' => $cashOut->count(),
                'cashOutTotal' => round((float) $cashOut->sum('amount'), 2),
            ],
        ];
    }

    /**
     * @return Collection<int, CashTransaction>
     */
    private function cashTransactions(CarbonInterface $from, CarbonInterface $to, CashFlow $type): Collection
    {
        return CashTransaction::query()
            ->with([
                'account:id,name',
                'visit.patient:id,name',
                'expense:id,title',
                'purchase.supplier:id,name',
            ])
            ->where('type', $type)
            ->whereBetween('created_at', [$from, $to])
            ->latest('id')
            ->get();
    }
}
