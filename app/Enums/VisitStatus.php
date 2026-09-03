<?php

namespace App\Enums;

enum VisitStatus: string
{
    case Registered = 'registered';
    case Paid = 'paid';
    case InLab = 'in_lab';
    case Completed = 'completed';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Registered => __('Registered'),
            self::Paid => __('Paid'),
            self::InLab => __('In lab'),
            self::Completed => __('Completed'),
            self::Delivered => __('Delivered'),
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Registered => 'slate',
            self::Paid => 'sky',
            self::InLab => 'amber',
            self::Completed => 'teal',
            self::Delivered => 'teal',
        };
    }
}
