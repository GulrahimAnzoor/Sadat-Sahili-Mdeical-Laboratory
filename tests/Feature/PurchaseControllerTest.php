<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_bill_updates_balance_and_stock(): void
    {
        $supplier = Supplier::factory()->create(['current_balance' => 0]);

        $response = $this->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'bill_number' => 'B-100',
            'billed_on' => '2026-08-31',
            'type' => 'simple',
            'received' => '50',
            'items' => [
                [
                    'name' => 'Glucose kit',
                    'quantity' => '2',
                    'unit_price' => '100',
                    'batch_number' => 'B1',
                    'expires_on' => '2027-01-01',
                ],
            ],
        ]);

        $purchase = Purchase::query()->first();

        $this->assertNotNull($purchase);
        $this->assertSame('200.00', $purchase->subtotal);
        $this->assertSame('150.00', $purchase->remaining);
        $this->assertSame('150.00', $supplier->fresh()->current_balance);
        $this->assertSame('2.00', InventoryItem::query()->firstWhere('name', 'Glucose kit')?->quantity);
        $this->assertSame('B1', InventoryItem::query()->firstWhere('name', 'Glucose kit')?->batch_number);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'in_purchase',
            'quantity' => '2.00',
            'purchase_id' => $purchase->id,
        ]);
        $this->assertDatabaseHas('cash_transactions', [
            'purchase_id' => $purchase->id,
            'type' => 'out',
            'amount' => '50.00',
        ]);
        $response->assertRedirect(route('purchases.show', $purchase));
    }

    public function test_store_rejects_bill_without_items(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->from(route('purchases.create'))->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'bill_number' => 'B-101',
            'billed_on' => '2026-08-31',
            'type' => 'simple',
            'received' => '0',
            'items' => [
                ['name' => '', 'quantity' => '', 'unit_price' => ''],
            ],
        ]);

        $response->assertRedirect(route('purchases.create'));
        $response->assertSessionHasErrors('items');
        $this->assertSame(0, Purchase::query()->count());
    }
}
