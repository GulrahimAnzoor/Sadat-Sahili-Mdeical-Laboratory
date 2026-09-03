<?php

namespace Tests\Feature;

use App\Enums\LabPermission;
use App\Models\InventoryCatalogItem;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_lists_existing_categories(): void
    {
        InventoryItem::factory()->create([
            'name' => 'Glucose kit',
            'category' => 'Reagents',
        ]);

        InventoryCatalogItem::factory()->create(['name' => 'Alcohol swab']);

        $this->get(route('inventory-items.create'))
            ->assertSee('Reagents')
            ->assertSee('Alcohol swab')
            ->assertSee('New entry')
            ->assertSee('Item name')
            ->assertSee('Add row')
            ->assertSee('Total value')
            ->assertSee('id="inventory-add-row"', false)
            ->assertSee('id="inventory-lot-template"', false)
            ->assertSee('id="inventory-supplier" name="supplier_id" required', false)
            ->assertSee('addRow?.addEventListener', false);
    }

    public function test_create_page_escapes_category_names(): void
    {
        InventoryCatalogItem::factory()->create([
            'name' => '<script>alert(1)</script>',
        ]);

        $this->get(route('inventory-items.create'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_store_saves_many_named_items_in_one_category(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Kabul Supply']);

        $this->from(route('inventory-items.create'))
            ->post(route('inventory-items.store'), [
                'category' => 'Reagents',
                'received_on' => '2026-09-03',
                'supplier_id' => $supplier->id,
                'lots' => [
                    [
                        'name' => 'EDTA tube',
                        'quantity' => '10',
                        'min_quantity' => '2',
                        'unit_cost' => '5',
                        'batch_number' => 'A1',
                        'expires_on' => '2027-01-01',
                    ],
                    [
                        'name' => 'Glucose kit',
                        'quantity' => '4',
                        'min_quantity' => '2',
                        'unit_cost' => '6',
                        'batch_number' => 'A2',
                        'expires_on' => '2027-02-01',
                    ],
                    [
                        'name' => '',
                        'quantity' => '',
                        'min_quantity' => '0',
                        'unit_cost' => '0',
                    ],
                ],
            ])
            ->assertRedirect(route('inventory-items.create'))
            ->assertSessionHas('success');

        $items = InventoryItem::query()->orderBy('id')->get();

        $this->assertSame(2, $items->count());
        $this->assertSame('EDTA tube', $items[0]->name);
        $this->assertSame('Reagents', $items[0]->category);
        $this->assertSame('10.00', $items[0]->quantity);
        $this->assertSame($supplier->id, $items[0]->supplier_id);
        $this->assertSame('Glucose kit', $items[1]->name);
        $this->assertSame('Reagents', $items[1]->category);
        $this->assertDatabaseHas('inventory_catalog_items', ['name' => 'Reagents']);
        $this->assertDatabaseMissing('inventory_catalog_items', ['name' => 'EDTA tube']);
    }

    public function test_store_rejects_a_category_without_named_rows(): void
    {
        $this->from(route('inventory-items.create'))
            ->post(route('inventory-items.store'), [
                'category' => 'Consumables',
                'received_on' => '2026-09-03',
                'lots' => [
                    ['name' => '', 'quantity' => '', 'min_quantity' => '0', 'unit_cost' => '0'],
                ],
            ])
            ->assertRedirect(route('inventory-items.create'))
            ->assertSessionHasErrors('lots');

        $this->assertSame(0, InventoryItem::query()->count());
    }

    public function test_store_rejects_a_missing_category(): void
    {
        $this->from(route('inventory-items.create'))
            ->post(route('inventory-items.store'), [
                'received_on' => '2026-09-03',
                'lots' => [[
                    'name' => 'Alcohol swab',
                    'quantity' => '3',
                    'min_quantity' => '0',
                    'unit_cost' => '1',
                ]],
            ])
            ->assertRedirect(route('inventory-items.create'))
            ->assertSessionHasErrors('category');

        $this->assertSame(0, InventoryItem::query()->count());
    }

    public function test_store_rejects_a_missing_supplier(): void
    {
        $this->from(route('inventory-items.create'))
            ->post(route('inventory-items.store'), [
                'category' => 'Reagents',
                'received_on' => '2026-09-03',
                'lots' => [[
                    'name' => 'Alcohol swab',
                    'quantity' => '3',
                    'min_quantity' => '0',
                    'unit_cost' => '1',
                ]],
            ])
            ->assertRedirect(route('inventory-items.create'))
            ->assertSessionHasErrors('supplier_id');

        $this->assertSame(0, InventoryItem::query()->count());
    }

    public function test_store_rejects_a_missing_date(): void
    {
        $supplier = Supplier::factory()->create();

        $this->from(route('inventory-items.create'))
            ->post(route('inventory-items.store'), [
                'category' => 'Reagents',
                'supplier_id' => $supplier->id,
                'lots' => [[
                    'name' => 'Alcohol swab',
                    'quantity' => '3',
                    'min_quantity' => '0',
                    'unit_cost' => '1',
                ]],
            ])
            ->assertRedirect(route('inventory-items.create'))
            ->assertSessionHasErrors('received_on');

        $this->assertSame(0, InventoryItem::query()->count());
    }

    public function test_guests_cannot_open_the_create_page(): void
    {
        $this->post(route('logout'));

        $this->get(route('inventory-items.create'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_without_inventory_permission_cannot_create_items(): void
    {
        $this->actingAs($this->staffUser([
            LabPermission::Dashboard->value,
        ]));

        $this->get(route('inventory-items.create'))
            ->assertForbidden();

        $this->post(route('inventory-items.store'), [
            'category' => 'Reagents',
            'received_on' => '2026-09-03',
            'lots' => [[
                'name' => 'Glucose kit',
                'quantity' => '1',
                'min_quantity' => '0',
                'unit_cost' => '1',
            ]],
        ])->assertForbidden();

        $this->assertSame(0, InventoryItem::query()->count());
    }
}
