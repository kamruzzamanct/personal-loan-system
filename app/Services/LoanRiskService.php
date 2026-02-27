<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EmploymentType;
use App\Enums\RiskLevel;

class LoanRiskService
{
    public function calculateRisk(float $loanAmount, float $monthlyIncome): string
    {
        if ($monthlyIncome <= 0) {
            return RiskLevel::VeryHigh->value;
        }

        $loanToIncomeRatio = $loanAmount / $monthlyIncome;

        // Very high repayment pressure: exceptionally large loan load against income.
        if ($loanToIncomeRatio >= 3.5 || ($loanAmount > 50000 && $monthlyIncome < 25000)) {
            return RiskLevel::VeryHigh->value;
        }

        // High repayment pressure: loan is at least twice the monthly income.
        if ($loanToIncomeRatio >= 2.0) {
            return RiskLevel::High->value;
        }

        // Medium repayment pressure: moderate stretch versus monthly income.
        if ($loanToIncomeRatio >= 1.75) {
            return RiskLevel::Medium->value;
        }

        return RiskLevel::Low->value;
    }

    public function isHighRisk(string $riskLevel): bool
    {
        return in_array($riskLevel, RiskLevel::highRiskValues(), true);
    }

    public function isSelfEmployed(EmploymentType|string $employmentType): bool
    {
        if (is_string($employmentType)) {
            return $employmentType === EmploymentType::SelfEmployed->value;
        }

        return $employmentType === EmploymentType::SelfEmployed;
    }
}
