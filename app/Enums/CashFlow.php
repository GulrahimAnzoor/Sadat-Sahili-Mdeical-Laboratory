<?php

namespace App\Enums;

enum CashFlow: string
{
    case In = 'in';
    case Out = 'out';

    public function label(): string
    {
        return match ($this) {
            self::In => __('Cash in'),
            self::Out => __('Cash out'),
        };
    }

    public function ledgerLabel(): string
    {
        return match ($this) {
            self::In => __('CREDIT'),
            self::Out => __('DEBIT'),
        };
    }
}
