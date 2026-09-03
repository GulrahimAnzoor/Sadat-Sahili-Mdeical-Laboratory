<?php

namespace App\Enums;

enum Sensitivity: string
{
    case Sensitive = 'S';
    case Intermediate = 'I';
    case Resistant = 'R';

    public function label(): string
    {
        return match ($this) {
            self::Sensitive => __('Sensitive'),
            self::Intermediate => __('Intermediate'),
            self::Resistant => __('Resistant'),
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Sensitive => 'text-emerald-700',
            self::Intermediate => 'text-slate-900',
            self::Resistant => 'text-red-700',
        };
    }
}
