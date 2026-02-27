<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\EmploymentType;
use App\Enums\RiskLevel;
use App\Services\LoanRiskService;
use PHPUnit\Framework\TestCase;

class LoanRiskServiceTest extends TestCase
{
    public function test_it_marks_application_as_high_risk_when_loan_is_at_least_twice_monthly_income(): void
    {
        $service = new LoanRiskService();

        $riskLevel = $service->calculateRisk(20000, 10000);

        $this->assertSame(RiskLevel::High->value, $riskLevel);
    }

    public function test_it_marks_application_as_high_risk_for_high_loan_and_low_income_threshold(): void
    {
        $service = new LoanRiskService();

        $riskLevel = $service->calculateRisk(60000, 20000);

        $this->assertSame(RiskLevel::VeryHigh->value, $riskLevel);
    }

    public function test_it_marks_application_as_low_risk_when_conditions_do_not_match_high_risk_rules(): void
    {
        $service = new LoanRiskService();

        $riskLevel = $service->calculateRisk(20000, 15000);

        $this->assertSame(RiskLevel::Low->value, $riskLevel);
    }

    public function test_it_marks_application_as_medium_risk_for_moderate_repayment_pressure(): void
    {
        $service = new LoanRiskService();

        $riskLevel = $service->calculateRisk(17500, 10000);

        $this->assertSame(RiskLevel::Medium->value, $riskLevel);
    }

    public function test_it_flags_high_and_very_high_as_high_risk_buckets(): void
    {
        $service = new LoanRiskService();

        $this->assertTrue($service->isHighRisk(RiskLevel::High->value));
        $this->assertTrue($service->isHighRisk(RiskLevel::VeryHigh->value));
        $this->assertFalse($service->isHighRisk(RiskLevel::Medium->value));
        $this->assertFalse($service->isHighRisk(RiskLevel::Low->value));
    }

    public function test_it_returns_true_for_self_employed_values(): void
    {
        $service = new LoanRiskService();

        $this->assertTrue($service->isSelfEmployed(EmploymentType::SelfEmployed));
        $this->assertTrue($service->isSelfEmployed(EmploymentType::SelfEmployed->value));
    }

    public function test_it_returns_false_for_non_self_employed_values(): void
    {
        $service = new LoanRiskService();

        $this->assertFalse($service->isSelfEmployed(EmploymentType::Salaried));
        $this->assertFalse($service->isSelfEmployed(EmploymentType::Salaried->value));
    }
}
