<?php

declare(strict_types=1);

namespace App\Enums;

enum RiskLevel: string
{
    case Low = 'low';
    case High = 'high';
}
