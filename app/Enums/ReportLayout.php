<?php

namespace App\Enums;

enum ReportLayout: string
{
    case Standard = 'standard';
    case UrineExam = 'urine_exam';
    case StoolExam = 'stool_exam';
    case TorchPanel = 'torch_panel';
    case Panel = 'panel';
    case Culture = 'culture';

    public function isSpecial(): bool
    {
        return $this !== self::Standard;
    }

    public function hidesStandardTitle(): bool
    {
        return match ($this) {
            self::UrineExam, self::StoolExam, self::TorchPanel => true,
            default => false,
        };
    }

    public function usesCultureFields(): bool
    {
        return $this === self::Culture;
    }
}
