<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\LoanApplicationStatus;
use App\Enums\RiskLevel;
use App\Http\Controllers\Controller;
use App\Jobs\SendLoanApprovedNotificationJob;
use App\Models\LoanApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class LoanApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $riskLevel = (string) $request->query('risk_level', '');
        $employmentType = (string) $request->query('employment_type', '');
        $user = $request->user();

        if (($search !== '' || $employmentType !== '') && ! $user?->can('filter applications')) {
            abort(403, 'You do not have permission to filter applications.');
        }

        if ($riskLevel === RiskLevel::Low->value && ! $user?->can('filter applications')) {
            abort(403, 'You do not have permission to filter low-risk applications.');
        }

        if (
            $riskLevel === RiskLevel::High->value
            && ! $user?->canAny(['filter applications', 'view high-risk only'])
        ) {
            abort(403, 'You do not have permission to view high-risk applications.');
        }

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

    public function show(LoanApplication $loanApplication): View
    {
        $loanApplication->loadMissing('approvedByUser');

        return view('admin.loan-applications.show', [
            'loanApplication' => $loanApplication,
        ]);
    }

    public function approve(Request $request, LoanApplication $loanApplication): RedirectResponse
    {
        $status = $loanApplication->status instanceof LoanApplicationStatus
            ? $loanApplication->status->value
            : (string) $loanApplication->status;

        if ($status === LoanApplicationStatus::Approved->value) {
            return redirect()
                ->route('admin.loan-applications.show', $loanApplication)
                ->with('success', 'Loan application is already approved.');
        }

        $loanApplication->update([
            'status' => LoanApplicationStatus::Approved->value,
            'approved_at' => now(),
            'approved_by_user_id' => $request->user()?->id,
        ]);

        SendLoanApprovedNotificationJob::dispatch($loanApplication->fresh());

        return redirect()
            ->route('admin.loan-applications.show', $loanApplication)
            ->with('success', 'Loan application approved successfully. Applicant notification is queued.');
    }
}
