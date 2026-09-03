<?php

namespace App\Support;

use App\Enums\ExpiryAlertStage;
use App\Models\InventoryItem;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\InventoryExpiryNotification;
use App\Notifications\InventoryLowStockNotification;
use App\Notifications\PatientRegisteredNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

final class LabAlerts
{
    public static function patientRegistered(Patient $patient): void
    {
        Notification::send(self::recipients(), new PatientRegisteredNotification($patient));
    }

    public static function stockReachedMinimum(InventoryItem $item, bool $wasAtMinimum): void
    {
        if ($wasAtMinimum || ! $item->hasReachedMinimum()) {
            return;
        }

        Notification::send(self::recipients(), new InventoryLowStockNotification($item));
    }

    public static function scanExpiryAlertsIfDue(): void
    {
        Cache::remember('inventory-expiry-scan:'.now()->toDateString(), now()->endOfDay(), function (): true {
            self::scanExpiryAlerts();

            return true;
        });
    }

    public static function scanExpiryAlerts(): int
    {
        $sent = 0;

        InventoryItem::query()
            ->whereNotNull('expires_on')
            ->orderBy('id')
            ->each(function (InventoryItem $item) use (&$sent): void {
                if (self::expiryFor($item)) {
                    $sent++;
                }
            });

        return $sent;
    }

    public static function expiryFor(InventoryItem $item): bool
    {
        $due = $item->dueExpiryStage();
        $current = $item->expiry_alert_stage;

        if ($due === null) {
            if ($current !== null) {
                $item->forceFill(['expiry_alert_stage' => null])->saveQuietly();
            }

            return false;
        }

        if ($current instanceof ExpiryAlertStage && $due->rank() <= $current->rank()) {
            return false;
        }

        Notification::send(self::recipients(), new InventoryExpiryNotification($item, $due));

        $item->forceFill(['expiry_alert_stage' => $due])->saveQuietly();

        return true;
    }

    /**
     * @return Collection<int, User>
     */
    private static function recipients(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }
}
