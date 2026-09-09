<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Rent = 'rent';
    case Electricity = 'electricity';
    case Fuel = 'fuel';
    case Salary = 'salary';
    case Meals = 'meals';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Rent => __('Rent'),
            self::Electricity => __('Electricity'),
            self::Fuel => __('Fuel'),
            self::Salary => __('Salary'),
            self::Meals => __('Meals'),
            self::Other => __('Other'),
        };
    }
}
