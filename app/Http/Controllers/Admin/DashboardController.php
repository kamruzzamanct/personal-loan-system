<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\LoanApplicationStatus;
use App\Enums\RiskLevel;
use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $summary = LoanApplication::query()
            ->selectRaw('COUNT(*) as total_applications')
            ->selectRaw(
                'SUM(CASE WHEN risk_level = ? THEN 1 ELSE 0 END) as high_risk_applications',
                [RiskLevel::High->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN employment_type = ? THEN 1 ELSE 0 END) as salaried_applications',
                [EmploymentType::Salaried->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN employment_type = ? THEN 1 ELSE 0 END) as self_employed_applications',
                [EmploymentType::SelfEmployed->value],
            )
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as approved_loans',
                [LoanApplicationStatus::Approved->value],
            )
            ->first();

        $totalApplications = (int) ($summary?->total_applications ?? 0);
        $approvedLoans = (int) ($summary?->approved_loans ?? 0);
        $highRiskApplications = (int) ($summary?->high_risk_applications ?? 0);
        $salariedApplications = (int) ($summary?->salaried_applications ?? 0);
        $selfEmployedApplications = (int) ($summary?->self_employed_applications ?? 0);

        $highRiskPercentage = $totalApplications > 0
            ? round(($highRiskApplications / $totalApplications) * 100, 2)
            : 0.0;

        $monthlyApplicationsChart = $this->monthlyApplicationsChart();

        $riskDistributionChart = [
            'labels' => ['High Risk', 'Low Risk'],
            'series' => [
                $highRiskApplications,
                max($totalApplications - $highRiskApplications, 0),
            ],
        ];

        $latestHighRiskApplications = LoanApplication::query()
            ->highRisk()
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'user' => $request->user(),
            'totalApplications' => $totalApplications,
            'approvedLoans' => $approvedLoans,
            'highRiskApplications' => $highRiskApplications,
            'salariedApplications' => $salariedApplications,
            'selfEmployedApplications' => $selfEmployedApplications,
            'highRiskPercentage' => $highRiskPercentage,
            'monthlyApplicationsChart' => $monthlyApplicationsChart,
            'riskDistributionChart' => $riskDistributionChart,
            'latestHighRiskApplications' => $latestHighRiskApplications,
        ]);
    }

    /**
     * @return array{labels: list<string>, series: list<int>}
     */
    private function monthlyApplicationsChart(): array
    {
        $startMonth = now()->startOfMonth()->subMonths(11);
        $endMonth = now()->startOfMonth();

        $monthExpression = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        $monthlyRows = LoanApplication::query()
            ->selectRaw("{$monthExpression} as month_key")
            ->selectRaw('COUNT(*) as applications_count')
            ->where('created_at', '>=', $startMonth)
            ->groupByRaw($monthExpression)
            ->orderByRaw($monthExpression)
            ->get()
            ->keyBy('month_key');

        $labels = [];
        $series = [];
        $cursor = $startMonth->copy();

        while ($cursor->lessThanOrEqualTo($endMonth)) {
            $monthKey = $cursor->format('Y-m');
            $labels[] = $cursor->format('M Y');
            $series[] = (int) ($monthlyRows->get($monthKey)->applications_count ?? 0);
            $cursor->addMonthNoOverflow();
        }

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }
}
