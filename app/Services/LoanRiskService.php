<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EmploymentType;
use App\Enums\RiskLevel;

class LoanRiskService
{
    public function calculateRisk(float $loanAmount, float $monthlyIncome): string
    {
        if ($loanAmount >= ($monthlyIncome * 2)) {
            return RiskLevel::High->value;
        }

        if ($loanAmount > 50000 && $monthlyIncome < 25000) {
            return RiskLevel::High->value;
        }

        return RiskLevel::Low->value;
    }

    public function isSelfEmployed(EmploymentType|string $employmentType): bool
    {
        if (is_string($employmentType)) {
            return $employmentType === EmploymentType::SelfEmployed->value;
        }

        return $employmentType === EmploymentType::SelfEmployed;
    }
}
