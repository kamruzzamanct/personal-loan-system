<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Enums\EmploymentType;
use App\Enums\LoanApplicationStatus;
use App\Enums\RiskLevel;
use App\Models\LoanApplication;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_dashboard_displays_aggregated_reporting_metrics_and_chart_data(): void
    {
        $this->actingAsAdmin();

        LoanApplication::factory()->create([
            'risk_level' => RiskLevel::High->value,
            'employment_type' => EmploymentType::Salaried->value,
            'is_self_employed' => false,
            'status' => LoanApplicationStatus::Approved->value,
            'created_at' => now()->subMonths(2)->startOfMonth()->addDay(),
        ]);

        LoanApplication::factory()->create([
            'risk_level' => RiskLevel::Low->value,
            'employment_type' => EmploymentType::SelfEmployed->value,
            'is_self_employed' => true,
            'status' => LoanApplicationStatus::Pending->value,
            'created_at' => now()->subMonth()->startOfMonth()->addDays(2),
        ]);

        LoanApplication::factory()->create([
            'risk_level' => RiskLevel::High->value,
            'employment_type' => EmploymentType::SelfEmployed->value,
            'is_self_employed' => true,
            'status' => LoanApplicationStatus::Pending->value,
            'created_at' => now()->subMonth()->startOfMonth()->addDays(4),
        ]);

        LoanApplication::factory()->create([
            'risk_level' => RiskLevel::Low->value,
            'employment_type' => EmploymentType::Salaried->value,
            'is_self_employed' => false,
            'status' => LoanApplicationStatus::Approved->value,
            'created_at' => now()->startOfMonth()->addDay(),
        ]);

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalApplications', 4);
        $response->assertViewHas('approvedLoans', 2);
        $response->assertViewHas('highRiskApplications', 2);
        $response->assertViewHas('salariedApplications', 2);
        $response->assertViewHas('selfEmployedApplications', 2);
        $response->assertViewHas('highRiskPercentage', 50.0);
        $response->assertViewHas('monthlyApplicationsChart', function (array $chart): bool {
            return count($chart['labels']) === 12
                && count($chart['series']) === 12
                && array_sum($chart['series']) === 4;
        });
        $response->assertViewHas('riskDistributionChart', function (array $chart): bool {
            return $chart['labels'] === ['High Risk', 'Low Risk']
                && $chart['series'] === [2, 2];
        });
    }

    public function test_dashboard_handles_empty_reporting_dataset(): void
    {
        $this->actingAsAdmin();

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('totalApplications', 0);
        $response->assertViewHas('approvedLoans', 0);
        $response->assertViewHas('highRiskApplications', 0);
        $response->assertViewHas('salariedApplications', 0);
        $response->assertViewHas('selfEmployedApplications', 0);
        $response->assertViewHas('highRiskPercentage', 0.0);
        $response->assertViewHas('monthlyApplicationsChart', function (array $chart): bool {
            return count($chart['labels']) === 12
                && count($chart['series']) === 12
                && array_sum($chart['series']) === 0;
        });
        $response->assertViewHas('riskDistributionChart', function (array $chart): bool {
            return $chart['series'] === [0, 0];
        });
    }

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create([
            'role' => AdminRole::SuperAdmin->value,
        ]);
        $user->assignRole('Super Admin');

        $this->actingAs($user);

        return $user;
    }
}
