<?php

declare(strict_types=1);

namespace App\Enums;

enum AdminRole: string
{
    case SuperAdmin = 'super_admin';
    case RiskManager = 'risk_manager';
    case Viewer = 'viewer';
    case Customer = 'customer';

    /**
     * @return list<string>
     */
    public static function adminValues(): array
    {
        return [
            self::SuperAdmin->value,
            self::RiskManager->value,
            self::Viewer->value,
        ];
    }
}
