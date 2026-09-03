<?php

namespace App\Notifications;

use App\Models\InventoryItem;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InventoryLowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public InventoryItem $item) {}

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
        return [
            'kind' => 'inventory.low_stock',
            'title' => __('Low stock'),
            'message' => __(':name has reached the minimum quantity (:minimum). Current quantity: :quantity.', [
                'name' => $this->item->name,
                'minimum' => $this->item->min_quantity,
                'quantity' => $this->item->quantity,
            ]),
            'url' => route('inventory-items.show', $this->item, false),
        ];
    }
}
