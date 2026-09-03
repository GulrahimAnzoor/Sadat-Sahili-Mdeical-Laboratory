<?php

namespace App\Support;

use App\Enums\CashFlow;
use App\Models\Account;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Visit;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class CashLedger
{
    public function defaultAccount(): Account
    {
        $account = Account::query()->isDefault()->first()
            ?? Account::query()->orderBy('id')->first();

        if ($account !== null) {
            return $account;
        }

        return Account::query()->create([
            'name' => 'Reception',
            'is_default' => true,
        ]);
    }

    /**
     * @param  array{account_id?: int, visit_id?: int|null, expense_id?: int|null, purchase_id?: int|null, type: CashFlow|string, amount: float|int|string, description?: string|null, occurred_at?: CarbonInterface|string|null}  $data
     */
    public function record(array $data): CashTransaction
    {
        $accountId = $data['account_id'] ?? $this->defaultAccount()->id;

        $transaction = CashTransaction::query()->create([
            'account_id' => $accountId,
            'visit_id' => $data['visit_id'] ?? null,
            'expense_id' => $data['expense_id'] ?? null,
            'purchase_id' => $data['purchase_id'] ?? null,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);

        if (isset($data['occurred_at']) && filled($data['occurred_at'])) {
            $occurredAt = Carbon::parse($data['occurred_at'])->startOfDay();
            $transaction->forceFill([
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ])->save();
        }

        return $transaction->refresh();
    }

    public function recordVisitPayment(Visit $visit, ?Account $account = null): ?CashTransaction
    {
        $paid = round((float) $visit->paid_amount, 2);

        if ($paid <= 0) {
            return null;
        }

        $alreadyRecorded = round((float) CashTransaction::query()
            ->where('visit_id', $visit->id)
            ->where('type', CashFlow::In)
            ->sum('amount'), 2);

        $delta = round($paid - $alreadyRecorded, 2);

        if ($delta <= 0) {
            return CashTransaction::query()
                ->where('visit_id', $visit->id)
                ->where('type', CashFlow::In)
                ->latest('id')
                ->first();
        }

        return $this->record([
            'account_id' => ($account ?? $this->defaultAccount())->id,
            'visit_id' => $visit->id,
            'type' => CashFlow::In,
            'amount' => $delta,
            'description' => __('Visit :token', ['token' => $visit->token_code ?? '#'.$visit->id]),
        ]);
    }

    public function syncExpense(Expense $expense): ?CashTransaction
    {
        return $this->syncDocumentOut(
            'expense_id',
            $expense->id,
            (float) $expense->amount,
            $expense->title,
            $expense->spent_on,
        );
    }

    public function syncPurchase(Purchase $purchase): ?CashTransaction
    {
        return $this->syncDocumentOut(
            'purchase_id',
            $purchase->id,
            (float) $purchase->received,
            __('Purchase :bill', ['bill' => $purchase->bill_number]),
            $purchase->billed_on,
        );
    }

    public function forgetExpense(Expense $expense): void
    {
        CashTransaction::query()->where('expense_id', $expense->id)->delete();
    }

    public function forgetPurchase(Purchase $purchase): void
    {
        CashTransaction::query()->where('purchase_id', $purchase->id)->delete();
    }

    public function labIncome(?CarbonInterface $from = null, ?CarbonInterface $to = null): float
    {
        return round((float) CashTransaction::query()
            ->where('type', CashFlow::In)
            ->whereNotNull('visit_id')
            ->when($from !== null && $to !== null, fn ($query) => $query->whereBetween('created_at', [$from, $to]))
            ->when($from !== null && $to === null, fn ($query) => $query->where('created_at', '>=', $from))
            ->sum('amount'), 2);
    }

    private function syncDocumentOut(
        string $foreignKey,
        int $id,
        float $amount,
        string $description,
        mixed $occurredAt,
    ): ?CashTransaction {
        $existing = CashTransaction::query()->where($foreignKey, $id)->first();
        $amount = round($amount, 2);

        if ($amount <= 0) {
            $existing?->delete();

            return null;
        }

        $occurred = Carbon::parse($occurredAt)->startOfDay();

        if ($existing !== null) {
            $existing->update([
                'type' => CashFlow::Out,
                'amount' => $amount,
                'description' => $description,
            ]);
            $existing->forceFill([
                'created_at' => $occurred,
                'updated_at' => $occurred,
            ])->save();

            return $existing->refresh();
        }

        return $this->record([
            $foreignKey => $id,
            'type' => CashFlow::Out,
            'amount' => $amount,
            'description' => $description,
            'occurred_at' => $occurred,
        ]);
    }
}
