<?php

namespace App\Models;

use App\Enums\CashFlow;
use Database\Factories\CashTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['account_id', 'visit_id', 'expense_id', 'purchase_id', 'type', 'amount', 'description'])]
class CashTransaction extends Model
{
    /** @use HasFactory<CashTransactionFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CashFlow::class,
            'amount' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function signedAmount(): float
    {
        $amount = (float) $this->amount;

        return $this->type === CashFlow::Out ? -$amount : $amount;
    }

    public function sourceUrl(): ?string
    {
        if ($this->visit_id !== null) {
            return route('visits.token', $this->visit_id);
        }

        if ($this->expense_id !== null) {
            return route('expenses.show', $this->expense_id);
        }

        if ($this->purchase_id !== null) {
            return route('purchases.show', $this->purchase_id);
        }

        return null;
    }
}
