<?php

namespace App\Enums;

enum ExpiryAlertStage: string
{
    case Month = 'month';
    case TenDays = 'ten_days';
    case Expired = 'expired';

    public function rank(): int
    {
        return match ($this) {
            self::Month => 1,
            self::TenDays => 2,
            self::Expired => 3,
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::Month => __('Expiry in one month'),
            self::TenDays => __('10 days until expiry'),
            self::Expired => __('Expired'),
        };
    }

    public function message(string $name, string $date, int $days = 0): string
    {
        return match ($this) {
            self::Month => __(':name will expire in one month (:date).', ['name' => $name, 'date' => $date]),
            self::TenDays => __(':name has :days days left before expiry (:date).', [
                'name' => $name,
                'days' => $days,
                'date' => $date,
            ]),
            self::Expired => __(':name has expired (:date).', ['name' => $name, 'date' => $date]),
        };
    }
}
