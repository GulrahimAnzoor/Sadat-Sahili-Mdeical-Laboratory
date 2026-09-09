<?php

use App\Models\StockMovement;
use App\Services\StockLedger;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(StockLedger::class)->backfillOpeningReceipts();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        StockMovement::query()->where('notes', 'Opening stock')->delete();
    }
};
