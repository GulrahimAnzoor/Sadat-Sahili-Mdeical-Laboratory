<?php

namespace Tests\Feature;

use App\Enums\LabPermission;
use App\Models\InventoryCatalogItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryCatalogItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_saves_an_item_name(): void
    {
        $this->get(route('settings.goods.index'))
            ->assertOk()
            ->assertSee('Item names')
            ->assertSee('Add name');

        $this->from(route('settings.goods.index'))
            ->post(route('settings.goods.store'), [
                'name' => 'Glucose kit',
            ])
            ->assertRedirect(route('settings.goods.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_catalog_items', ['name' => 'Glucose kit']);

        $this->get(route('inventory-items.create'))
            ->assertSee('Glucose kit')
            ->assertSee('id="inventory-category-list"', false);
    }

    public function test_settings_page_rejects_a_duplicate_item_name(): void
    {
        InventoryCatalogItem::factory()->create(['name' => 'EDTA tube']);

        $this->from(route('settings.goods.index'))
            ->post(route('settings.goods.store'), [
                'name' => 'EDTA tube',
            ])
            ->assertRedirect(route('settings.goods.index'))
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('inventory_catalog_items', 1);
    }

    public function test_settings_page_escapes_item_names(): void
    {
        InventoryCatalogItem::factory()->create([
            'name' => '<script>alert(1)</script>',
        ]);

        $this->get(route('settings.goods.index'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_deleting_an_item_name_removes_it(): void
    {
        $item = InventoryCatalogItem::factory()->create(['name' => 'Alcohol swab']);

        $this->delete(route('settings.goods.destroy', $item))
            ->assertRedirect(route('settings.goods.index'));

        $this->assertModelMissing($item);
    }

    public function test_staff_without_settings_cannot_manage_item_names(): void
    {
        $this->actingAs($this->staffUser([
            LabPermission::Dashboard->value,
        ]));

        $this->get(route('settings.goods.index'))
            ->assertForbidden();

        $this->post(route('settings.goods.store'), [
            'name' => 'Glucose kit',
        ])->assertForbidden();

        $this->assertDatabaseCount('inventory_catalog_items', 0);
    }

    public function test_guests_cannot_open_item_names(): void
    {
        $this->post(route('logout'));

        $this->get(route('settings.goods.index'))
            ->assertRedirect(route('login'));
    }
}
