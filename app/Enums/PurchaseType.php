<?php

namespace App\Enums;

enum PurchaseType: string
{
    case Simple = 'simple';
    case Reagent = 'reagent';

    public function label(): string
    {
        return match ($this) {
            self::Simple => __('Simple bill'),
            self::Reagent => __('Reagent bill'),
        };
    }
}
