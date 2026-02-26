<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\LoanApplicationStatus;
use App\Enums\RiskLevel;
use App\Exports\LoanApplicationsExport;
use App\Http\Controllers\Controller;
use App\Jobs\SendLoanApprovedNotificationJob;
use App\Models\LoanApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LoanApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filtersFromRequest($request);

        $loanApplications = $this->filteredQuery($request, $filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.loan-applications.index', [
            'loanApplications' => $loanApplications,
            'filters' => $filters,
        ]);
    }

    public function export(Request $request, string $format): BinaryFileResponse
    {
        $filters = $this->filtersFromRequest($request);

        $writerType = $format === 'csv' ? ExcelWriter::CSV : ExcelWriter::XLSX;
        $fileName = 'loan-applications-'.now()->format('Ymd_His').'.'.$format;
        $query = $this->filteredQuery($request, $filters)->latest();

        return Excel::download(new LoanApplicationsExport($query), $fileName, $writerType);
    }

    /**
     * @return array{search: string, risk_level: string, employment_type: string}
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'risk_level' => (string) $request->query('risk_level', ''),
            'employment_type' => (string) $request->query('employment_type', ''),
        ];
    }

    /**
     * @param  array{search: string, risk_level: string, employment_type: string}  $filters
     */
    private function authorizeFilters(Request $request, array $filters): void
    {
        $user = $request->user();
        $search = $filters['search'];
        $riskLevel = $filters['risk_level'];
        $employmentType = $filters['employment_type'];

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
    }

    /**
     * @param  array{search: string, risk_level: string, employment_type: string}  $filters
     */
    private function filteredQuery(Request $request, array $filters): Builder
    {
        $this->authorizeFilters($request, $filters);

        $search = $filters['search'];
        $riskLevel = $filters['risk_level'];
        $employmentType = $filters['employment_type'];
        $query = LoanApplication::query();

        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search): void {
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

        return $query;
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
