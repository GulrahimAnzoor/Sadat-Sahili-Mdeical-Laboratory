<?php

namespace Tests\Feature;

use App\Enums\ExpiryAlertStage;
use App\Models\InventoryItem;
use App\Models\Patient;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\InventoryExpiryNotification;
use App\Notifications\InventoryLowStockNotification;
use App\Notifications\PatientRegisteredNotification;
use App\Support\LabAlerts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_a_patient_shows_a_notification_in_the_inbox(): void
    {
        $this->post(route('patients.store'), [
            'name' => 'Karim',
            'father_name' => 'Najib',
            'gender' => 'male',
        ])->assertRedirect();

        $this->assertSame(1, auth()->user()?->notifications()->count());
        $this->assertSame(
            PatientRegisteredNotification::class,
            auth()->user()?->notifications()->value('type'),
        );

        $this->get(route('notifications.index'))
            ->assertSee('New patient registered')
            ->assertSee('Karim has been registered.')
            ->assertDontSee('No notifications');
    }

    public function test_inactive_users_do_not_receive_patient_notifications(): void
    {
        User::factory()->inactive()->create();

        $this->post(route('patients.store'), [
            'name' => 'Amina',
            'father_name' => 'Salim',
            'gender' => 'female',
        ])->assertRedirect();

        $this->assertSame(1, auth()->user()?->notifications()->count());
        $this->assertSame(0, User::query()->where('is_active', false)->first()?->notifications()->count());
    }

    public function test_creating_an_item_at_the_minimum_quantity_notifies_staff(): void
    {
        $supplier = Supplier::factory()->create();

        $this->post(route('inventory-items.store'), [
            'category' => 'Reagents',
            'received_on' => '2026-09-03',
            'supplier_id' => $supplier->id,
            'lots' => [[
                'name' => 'Glucose kit',
                'quantity' => '5',
                'min_quantity' => '5',
                'unit_cost' => '12',
            ]],
        ])->assertRedirect();

        $this->assertSame(1, auth()->user()?->notifications()->count());
        $this->assertSame(
            InventoryLowStockNotification::class,
            auth()->user()?->notifications()->value('type'),
        );

        $this->get(route('notifications.index'))
            ->assertSee('Low stock')
            ->assertSee('Glucose kit has reached the minimum quantity');
    }

    public function test_lowering_stock_to_the_minimum_notifies_once(): void
    {
        $item = InventoryItem::factory()->create([
            'name' => 'EDTA tube',
            'quantity' => 20,
            'min_quantity' => 5,
        ]);

        $this->assertSame(0, auth()->user()?->notifications()->count());

        $this->put(route('inventory-items.update', $item), [
            'name' => $item->name,
            'quantity' => '5',
            'min_quantity' => '5',
            'unit_cost' => $item->unit_cost,
        ])->assertRedirect();

        $this->assertSame(1, auth()->user()?->notifications()->count());

        $this->put(route('inventory-items.update', $item), [
            'name' => $item->name,
            'quantity' => '4',
            'min_quantity' => '5',
            'unit_cost' => $item->unit_cost,
        ])->assertRedirect();

        $this->assertSame(1, auth()->user()?->notifications()->count());
    }

    public function test_creating_an_item_above_the_minimum_does_not_notify(): void
    {
        $supplier = Supplier::factory()->create();

        $this->post(route('inventory-items.store'), [
            'category' => 'Consumables',
            'received_on' => '2026-09-03',
            'supplier_id' => $supplier->id,
            'lots' => [[
                'name' => 'Alcohol swab',
                'quantity' => '40',
                'min_quantity' => '10',
                'unit_cost' => '1',
            ]],
        ])->assertRedirect();

        $this->assertSame(0, auth()->user()?->notifications()->count());
    }

    public function test_item_expiring_in_one_month_notifies_staff(): void
    {
        $this->travelTo('2026-09-03 09:00:00');

        InventoryItem::factory()->create([
            'name' => 'Reagent A',
            'quantity' => 20,
            'min_quantity' => 0,
            'expires_on' => '2026-10-03',
        ]);

        $this->assertSame(1, auth()->user()?->notifications()->count());
        $this->assertSame(
            InventoryExpiryNotification::class,
            auth()->user()?->notifications()->value('type'),
        );
        $this->assertSame(
            ExpiryAlertStage::Month,
            InventoryItem::query()->first()?->expiry_alert_stage,
        );

        $this->get(route('notifications.index'))
            ->assertSee('Expiry in one month')
            ->assertSee('Reagent A will expire in one month (2026-10-03).');
    }

    public function test_the_same_expiry_stage_does_not_notify_again(): void
    {
        $this->travelTo('2026-09-03 09:00:00');

        $item = InventoryItem::factory()->create([
            'name' => 'Reagent B',
            'quantity' => 20,
            'min_quantity' => 0,
            'expires_on' => '2026-10-03',
        ]);

        $this->put(route('inventory-items.update', $item), [
            'name' => $item->name,
            'quantity' => '20',
            'min_quantity' => '0',
            'unit_cost' => $item->unit_cost,
            'expires_on' => '2026-10-03',
        ])->assertRedirect();

        $this->assertSame(1, auth()->user()?->notifications()->count());
    }

    public function test_expiry_alerts_advance_to_ten_days_then_expired(): void
    {
        $this->travelTo('2026-09-03 09:00:00');

        InventoryItem::factory()->create([
            'name' => 'Strip kit',
            'quantity' => 20,
            'min_quantity' => 0,
            'expires_on' => '2026-10-03',
        ]);

        $this->travelTo('2026-09-23 09:00:00');
        LabAlerts::scanExpiryAlerts();

        $this->travelTo('2026-10-03 09:00:00');
        LabAlerts::scanExpiryAlerts();

        $this->assertSame(3, auth()->user()?->notifications()->count());

        $this->get(route('notifications.index'))
            ->assertSee('Strip kit has 10 days left before expiry (2026-10-03).')
            ->assertSee('Strip kit has expired (2026-10-03).');
    }

    public function test_item_first_seen_within_ten_days_skips_the_month_alert(): void
    {
        $this->travelTo('2026-09-03 09:00:00');

        InventoryItem::factory()->create([
            'name' => 'Control serum',
            'quantity' => 20,
            'min_quantity' => 0,
            'expires_on' => '2026-09-08',
        ]);

        $this->assertSame(1, auth()->user()?->notifications()->count());
        $this->assertSame(
            ExpiryAlertStage::TenDays,
            InventoryItem::query()->first()?->expiry_alert_stage,
        );

        $this->get(route('notifications.index'))
            ->assertSee('Control serum has 5 days left before expiry (2026-09-08).')
            ->assertDontSee('will expire in one month');
    }

    public function test_moving_expiry_beyond_one_month_clears_the_alert_stage(): void
    {
        $this->travelTo('2026-09-03 09:00:00');

        $item = InventoryItem::factory()->create([
            'quantity' => 20,
            'min_quantity' => 0,
            'expires_on' => '2026-10-03',
        ]);

        $item->update(['expires_on' => '2026-12-03']);

        $this->assertNull($item->fresh()->expiry_alert_stage);
        $this->assertSame(1, auth()->user()?->notifications()->count());
    }

    public function test_expiry_command_notifies_when_an_item_enters_the_window(): void
    {
        $this->travelTo('2026-09-03 09:00:00');

        InventoryItem::factory()->create([
            'name' => 'Culture media',
            'quantity' => 20,
            'min_quantity' => 0,
            'expires_on' => '2026-12-03',
        ]);

        $this->assertSame(0, auth()->user()?->notifications()->count());

        $this->travelTo('2026-11-03 09:00:00');

        $this->artisan('inventory:check-expiry')
            ->assertSuccessful();

        $this->assertSame(1, auth()->user()?->notifications()->count());
        $this->assertSame(
            ExpiryAlertStage::Month,
            InventoryItem::query()->first()?->expiry_alert_stage,
        );
    }

    public function test_opening_the_inbox_scans_due_expiry_alerts(): void
    {
        $this->travelTo('2026-09-03 09:00:00');

        InventoryItem::factory()->create([
            'name' => 'Buffer',
            'quantity' => 20,
            'min_quantity' => 0,
            'expires_on' => '2026-12-03',
        ]);

        $this->travelTo('2026-11-03 09:00:00');

        $this->get(route('notifications.index'))
            ->assertSee('Buffer will expire in one month (2026-12-03).');
    }

    public function test_inbox_escapes_item_names_in_notifications(): void
    {
        $this->travelTo('2026-09-03 09:00:00');

        InventoryItem::factory()->create([
            'name' => '<script>alert(1)</script>',
            'quantity' => 20,
            'min_quantity' => 0,
            'expires_on' => '2026-10-03',
        ]);

        $this->get(route('notifications.index'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_opening_a_notification_deletes_it_and_opens_the_record(): void
    {
        $this->post(route('patients.store'), [
            'name' => 'Zahra',
            'father_name' => 'Omar',
            'gender' => 'female',
        ])->assertRedirect();

        $patient = Patient::query()->firstWhere('name', 'Zahra');
        $notification = auth()->user()?->notifications()->first();

        $this->assertNotNull($patient);
        $this->assertNotNull($notification);

        $this->post(route('notifications.open', $notification))
            ->assertRedirect(route('patients.show', $patient));

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_opening_another_users_notification_returns_404(): void
    {
        $other = User::factory()->create();

        $this->post(route('patients.store'), [
            'name' => 'Bilal',
            'father_name' => 'Karim',
            'gender' => 'male',
        ])->assertRedirect();

        $foreign = $other->notifications()->first();

        $this->assertNotNull($foreign);

        $this->post(route('notifications.open', $foreign))
            ->assertNotFound();
    }

    public function test_clearing_all_notifications_deletes_them(): void
    {
        $this->post(route('patients.store'), [
            'name' => 'Nadia',
            'father_name' => 'Hassan',
            'gender' => 'female',
        ])->assertRedirect();

        $this->from(route('notifications.index'))
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('notifications.index'));

        $this->assertSame(0, auth()->user()?->notifications()->count());
    }

    public function test_guests_cannot_open_or_view_notifications(): void
    {
        $this->post(route('logout'));

        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));

        $this->post(route('notifications.read-all'))
            ->assertRedirect(route('login'));
    }
}
