<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
        });

        $this->backfillLedger();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_id');
            $table->dropConstrainedForeignId('purchase_id');
        });
    }

    private function backfillLedger(): void
    {
        $accountId = DB::table('accounts')->where('is_default', true)->value('id')
            ?? DB::table('accounts')->orderBy('id')->value('id');

        if ($accountId === null) {
            return;
        }

        $now = now();

        foreach (DB::table('visits')->where('paid_amount', '>', 0)->orderBy('id')->get() as $visit) {
            $exists = DB::table('cash_transactions')
                ->where('visit_id', $visit->id)
                ->where('type', 'in')
                ->exists();

            if ($exists) {
                continue;
            }

            $createdAt = $visit->updated_at ?? $visit->created_at ?? $now;

            DB::table('cash_transactions')->insert([
                'account_id' => $accountId,
                'visit_id' => $visit->id,
                'type' => 'in',
                'amount' => $visit->paid_amount,
                'description' => 'Visit '.($visit->token_code ?: '#'.$visit->id),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        foreach (DB::table('expenses')->where('amount', '>', 0)->orderBy('id')->get() as $expense) {
            $exists = DB::table('cash_transactions')
                ->where('expense_id', $expense->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $occurredAt = $expense->spent_on.' 00:00:00';

            DB::table('cash_transactions')->insert([
                'account_id' => $accountId,
                'expense_id' => $expense->id,
                'type' => 'out',
                'amount' => $expense->amount,
                'description' => $expense->title,
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ]);
        }

        foreach (DB::table('purchases')->where('received', '>', 0)->orderBy('id')->get() as $purchase) {
            $exists = DB::table('cash_transactions')
                ->where('purchase_id', $purchase->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $occurredAt = $purchase->billed_on.' 00:00:00';

            DB::table('cash_transactions')->insert([
                'account_id' => $accountId,
                'purchase_id' => $purchase->id,
                'type' => 'out',
                'amount' => $purchase->received,
                'description' => 'Purchase '.$purchase->bill_number,
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ]);
        }
    }
};
