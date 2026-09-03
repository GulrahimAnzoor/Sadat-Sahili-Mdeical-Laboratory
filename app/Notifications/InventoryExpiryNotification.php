<?php

namespace App\Notifications;

use App\Enums\ExpiryAlertStage;
use App\Models\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InventoryExpiryNotification extends Notification
{
    use Queueable;

    public function __construct(public InventoryItem $item, public ExpiryAlertStage $stage) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{kind: string, title: string, message: string, url: string}
     */
    public function toArray(object $notifiable): array
    {
        $date = $this->item->expires_on?->toDateString() ?? '';
        $days = 0;

        if ($this->item->expires_on !== null) {
            $days = max(0, (int) now()->startOfDay()->diffInDays($this->item->expires_on->copy()->startOfDay(), false));
        }

        return [
            'kind' => 'inventory.expiry.'.$this->stage->value,
            'title' => $this->stage->title(),
            'message' => $this->stage->message((string) $this->item->name, $date, $days),
            'url' => route('inventory-items.show', $this->item, false),
        ];
    }
}
