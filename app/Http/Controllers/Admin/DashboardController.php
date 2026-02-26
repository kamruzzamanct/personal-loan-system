<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $totalApplications = LoanApplication::query()->count();
        $approvedLoans = LoanApplication::query()->approved()->count();
        $highRiskApplications = LoanApplication::query()->highRisk()->count();
        $salariedApplications = LoanApplication::query()->salaried()->count();
        $selfEmployedApplications = LoanApplication::query()->selfEmployed()->count();

        $highRiskPercentage = $totalApplications > 0
            ? round(($highRiskApplications / $totalApplications) * 100, 2)
            : 0.0;

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
            'latestHighRiskApplications' => $latestHighRiskApplications,
        ]);
    }
}
