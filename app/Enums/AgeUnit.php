<?php

namespace App\Enums;

enum AgeUnit: string
{
    case Years = 'y';
    case Months = 'm';

    public function label(): string
    {
        return match ($this) {
            self::Years => __('Years'),
            self::Months => __('Months'),
        };
    }

    public function min(): int
    {
        return match ($this) {
            self::Years => 0,
            self::Months => 1,
        };
    }

    public function max(): int
    {
        return match ($this) {
            self::Years => 120,
            self::Months => 23,
        };
    }
}
