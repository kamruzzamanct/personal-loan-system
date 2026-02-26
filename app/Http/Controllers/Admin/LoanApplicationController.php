<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\RiskLevel;
use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LoanApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $riskLevel = (string) $request->query('risk_level', '');
        $employmentType = (string) $request->query('employment_type', '');

        $query = LoanApplication::query();

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (in_array($riskLevel, [RiskLevel::High->value, RiskLevel::Low->value], true)) {
            match ($riskLevel) {
                RiskLevel::High->value => $query->highRisk(),
                RiskLevel::Low->value => $query->lowRisk(),
            };
        }

        if (in_array($employmentType, [EmploymentType::Salaried->value, EmploymentType::SelfEmployed->value], true)) {
            match ($employmentType) {
                EmploymentType::Salaried->value => $query->salaried(),
                EmploymentType::SelfEmployed->value => $query->selfEmployed(),
            };
        }

        $loanApplications = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.loan-applications.index', [
            'loanApplications' => $loanApplications,
            'filters' => [
                'search' => $search,
                'risk_level' => $riskLevel,
                'employment_type' => $employmentType,
            ],
        ]);
    }
}
