<?php

namespace Tests\Feature;

use App\Enums\LabPermission;
use App\Enums\StockMovementType;
use App\Models\InventoryItem;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Test;
use App\Models\Visit;
use App\Services\StockLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_creates_separate_lots_and_inbound_movements(): void
    {
        $supplier = Supplier::factory()->create(['current_balance' => 0]);

        $this->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'bill_number' => 'B-200',
            'billed_on' => '2026-09-01',
            'type' => 'simple',
            'received' => '0',
            'items' => [
                [
                    'name' => 'ASO kit',
                    'quantity' => '10',
                    'unit_price' => '20',
                    'batch_number' => 'A1',
                    'expires_on' => '2026-10-01',
                ],
                [
                    'name' => 'ASO kit',
                    'quantity' => '5',
                    'unit_price' => '20',
                    'batch_number' => 'B2',
                    'expires_on' => '2027-01-01',
                ],
            ],
        ])->assertRedirect();

        $lots = InventoryItem::query()->where('name', 'ASO kit')->orderBy('batch_number')->get();

        $this->assertSame(2, $lots->count());
        $this->assertSame('10.00', $lots[0]->quantity);
        $this->assertSame('5.00', $lots[1]->quantity);
        $this->assertSame(2, StockMovement::query()->inbound()->count());
        $this->assertEquals(15, StockMovement::query()->inbound()->sum('quantity'));
    }

    public function test_saving_test_results_deducts_selected_stock(): void
    {
        $lot = InventoryItem::factory()->create([
            'name' => 'ASO kit',
            'quantity' => 8,
            'expires_on' => '2027-01-01',
        ]);
        [$visit, $patientTest] = $this->visitWithTest();

        $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.results.store', $visit), [
                'results' => [[
                    'patient_test_id' => $patientTest->id,
                    'consume_materials' => '1',
                    'extra_rows' => [
                        ['name' => 'ASO', 'value' => 'good', 'unit' => 'IU/mL', 'normal_range' => '<200'],
                    ],
                    'materials' => [
                        ['inventory_item_id' => $lot->id, 'quantity' => '2'],
                    ],
                ]],
            ])
            ->assertRedirect();

        $this->assertSame('6.00', $lot->fresh()->quantity);
        $this->assertSame(1, StockMovement::query()->where('type', StockMovementType::OutTest)->count());
        $this->assertSame($patientTest->id, (int) StockMovement::query()->value('patient_test_id'));
    }

    public function test_saving_the_same_test_materials_twice_does_not_double_deduct(): void
    {
        $lot = InventoryItem::factory()->create(['name' => 'EDTA tube', 'quantity' => 10]);
        [$visit, $patientTest] = $this->visitWithTest();

        $payload = [
            'results' => [[
                'patient_test_id' => $patientTest->id,
                'consume_materials' => '1',
                'extra_rows' => [
                    ['name' => 'ASO', 'value' => '1', 'unit' => '', 'normal_range' => ''],
                ],
                'materials' => [
                    ['inventory_item_id' => $lot->id, 'quantity' => '3'],
                ],
            ]],
        ];

        $this->post(route('visits.results.store', $visit), $payload)->assertRedirect();
        $this->post(route('visits.results.store', $visit), $payload)->assertRedirect();

        $this->assertSame('7.00', $lot->fresh()->quantity);
        $this->assertSame(1, StockMovement::query()->where('type', StockMovementType::OutTest)->count());
        $this->assertSame('3.00', StockMovement::query()->where('type', StockMovementType::OutTest)->value('quantity'));
    }

    public function test_saving_test_materials_rejects_when_stock_is_insufficient(): void
    {
        $lot = InventoryItem::factory()->create(['name' => 'Alcohol swab', 'quantity' => 1]);
        [$visit, $patientTest] = $this->visitWithTest();

        $this->from(route('visits.results.edit', $visit))
            ->post(route('visits.results.store', $visit), [
                'results' => [[
                    'patient_test_id' => $patientTest->id,
                    'consume_materials' => '1',
                    'extra_rows' => [
                        ['name' => 'ASO', 'value' => '1', 'unit' => '', 'normal_range' => ''],
                    ],
                    'materials' => [
                        ['inventory_item_id' => $lot->id, 'quantity' => '5'],
                    ],
                ]],
            ])
            ->assertRedirect(route('visits.results.edit', $visit))
            ->assertSessionHasErrors('quantity');

        $this->assertSame('1.00', $lot->fresh()->quantity);
        $this->assertSame(0, StockMovement::query()->outbound()->count());
    }

    public function test_lab_usage_deducts_stock_without_a_visit(): void
    {
        $lot = InventoryItem::factory()->create(['name' => 'Gloves', 'quantity' => 20]);

        $this->from(route('stock-usages.create'))
            ->post(route('stock-usages.store'), [
                'used_on' => '2026-09-09',
                'notes' => 'Quality control',
                'items' => [
                    ['inventory_item_id' => $lot->id, 'quantity' => '4'],
                ],
            ])
            ->assertRedirect(route('inventory-items.index'));

        $this->assertSame('16.00', $lot->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $lot->id,
            'type' => StockMovementType::OutUsage->value,
            'quantity' => '4.00',
            'notes' => 'Quality control',
        ]);

        $this->get(route('inventory-items.index'))
            ->assertOk()
            ->assertSee('href="'.e(route('stock-usages.create')).'"', false)
            ->assertSee('4.00')
            ->assertSee('16.00');
    }

    public function test_inventory_page_opens_lab_usage_and_sidebar_does_not_list_it(): void
    {
        $this->get(route('inventory-items.index'))
            ->assertOk()
            ->assertSee('href="'.e(route('stock-usages.create')).'"', false);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('stock-usages.create'), false);
    }

    public function test_opening_stock_without_movements_is_counted_as_received(): void
    {
        $lot = InventoryItem::factory()->create([
            'name' => 'TSH kit',
            'quantity' => 12,
            'received_on' => '2026-08-01',
        ]);

        $this->assertSame(1, app(StockLedger::class)->backfillOpeningReceipts());

        $this->get(route('inventory-items.index'))
            ->assertOk()
            ->assertSee('12.00');

        $this->assertDatabaseHas('stock_movements', [
            'inventory_item_id' => $lot->id,
            'type' => StockMovementType::InManual->value,
            'quantity' => '12.00',
            'notes' => 'Opening stock',
        ]);
    }

    public function test_inventory_index_shows_received_consumed_and_on_hand(): void
    {
        $lot = InventoryItem::factory()->create(['name' => 'Glucose kit', 'quantity' => 7]);
        StockMovement::factory()->create([
            'inventory_item_id' => $lot->id,
            'type' => StockMovementType::InManual,
            'quantity' => 10,
            'occurred_on' => '2026-09-01',
        ]);
        StockMovement::factory()->create([
            'inventory_item_id' => $lot->id,
            'type' => StockMovementType::OutUsage,
            'quantity' => 3,
            'occurred_on' => '2026-09-08',
        ]);

        $this->get(route('inventory-items.index'))
            ->assertOk()
            ->assertSee('Received')
            ->assertSee('Consumed')
            ->assertSee('On hand')
            ->assertSee('10.00')
            ->assertSee('3.00')
            ->assertSee('7.00');
    }

    public function test_results_page_includes_materials_picker(): void
    {
        InventoryItem::factory()->create(['name' => 'ASO kit', 'quantity' => 4]);
        [$visit] = $this->visitWithTest();

        $this->get(route('visits.results.edit', $visit))
            ->assertOk()
            ->assertSee('Materials used')
            ->assertSee('ASO kit')
            ->assertSee('Add from stock')
            ->assertDontSee('Share')
            ->assertDontSee('Handover');
    }

    public function test_fefo_consume_by_name_uses_the_earliest_expiry_lot(): void
    {
        $soon = InventoryItem::factory()->create([
            'name' => 'Reagent X',
            'quantity' => 4,
            'expires_on' => '2026-10-01',
        ]);
        $later = InventoryItem::factory()->create([
            'name' => 'Reagent X',
            'quantity' => 4,
            'expires_on' => '2027-10-01',
        ]);

        DB::transaction(function (): void {
            app(StockLedger::class)->consumeByName(
                'Reagent X',
                5,
                StockMovementType::OutUsage,
                ['occurred_on' => '2026-09-09'],
            );
        });

        $this->assertSame('0.00', $soon->fresh()->quantity);
        $this->assertSame('3.00', $later->fresh()->quantity);
    }

    public function test_staff_without_inventory_permission_cannot_record_lab_usage(): void
    {
        $this->actingAs($this->staffUser([
            LabPermission::Lab->value,
        ]));

        $this->get(route('stock-usages.create'))->assertForbidden();
        $this->post(route('stock-usages.store'), [
            'used_on' => now()->toDateString(),
            'items' => [['inventory_item_id' => 1, 'quantity' => '1']],
        ])->assertForbidden();
    }

    /**
     * @return array{0: Visit, 1: PatientTest}
     */
    private function visitWithTest(): array
    {
        $patient = Patient::factory()->create();
        $test = Test::factory()->create(['name' => 'ASO']);
        $visit = Visit::factory()->for($patient)->create();
        $patientTest = PatientTest::factory()->for($visit)->for($patient)->for($test)->create();

        return [$visit, $patientTest];
    }
}
