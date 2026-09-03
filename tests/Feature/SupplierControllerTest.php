<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_supplier_and_redirects_to_show(): void
    {
        $response = $this->post(route('suppliers.store'), [
            'name' => 'Kabul Medical Supply',
            'phone' => '0700000000',
        ]);

        $supplier = Supplier::query()->firstWhere('name', 'Kabul Medical Supply');

        $this->assertNotNull($supplier);
        $response->assertRedirect(route('suppliers.show', $supplier));
    }

    public function test_store_rejects_missing_name(): void
    {
        $response = $this->from(route('suppliers.create'))->post(route('suppliers.store'), []);

        $response->assertRedirect(route('suppliers.create'));
        $response->assertSessionHasErrors('name');
        $this->assertSame(0, Supplier::query()->count());
    }
}
