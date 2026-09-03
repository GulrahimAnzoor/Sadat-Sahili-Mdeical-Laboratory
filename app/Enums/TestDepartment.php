<?php

namespace App\Enums;

enum TestDepartment: string
{
    case Routine = 'routine';
    case RoutineChemistry = 'routine_chemistry';
    case SpecialChemistry = 'special_chemistry';
    case Microbiology = 'microbiology';
    case Pathology = 'pathology';

    public function label(): string
    {
        return match ($this) {
            self::Routine => __('Routine'),
            self::RoutineChemistry => __('Routine chemistry'),
            self::SpecialChemistry => __('Special chemistry'),
            self::Microbiology => __('Microbiology'),
            self::Pathology => __('Pathology'),
        };
    }

    public function isActiveByDefault(): bool
    {
        return match ($this) {
            self::Microbiology, self::Pathology => false,
            default => true,
        };
    }
}
