<?php

declare(strict_types=1);

namespace App\Enums;

enum RiskLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case VeryHigh = 'very_high';

    /**
     * @return list<string>
     */
    public static function highRiskValues(): array
    {
        return [
            self::High->value,
            self::VeryHigh->value,
        ];
    }
}
