<?php

namespace App\Enums;

enum StockMovementType: string
{
    case InPurchase = 'in_purchase';
    case InManual = 'in_manual';
    case OutTest = 'out_test';
    case OutUsage = 'out_usage';

    public function label(): string
    {
        return match ($this) {
            self::InPurchase => __('Purchase received'),
            self::InManual => __('Stock received'),
            self::OutTest => __('Used on test'),
            self::OutUsage => __('Lab usage'),
        };
    }

    public function isInbound(): bool
    {
        return match ($this) {
            self::InPurchase, self::InManual => true,
            self::OutTest, self::OutUsage => false,
        };
    }

    /**
     * @return list<self>
     */
    public static function inbound(): array
    {
        return [self::InPurchase, self::InManual];
    }

    /**
     * @return list<self>
     */
    public static function outbound(): array
    {
        return [self::OutTest, self::OutUsage];
    }
}
