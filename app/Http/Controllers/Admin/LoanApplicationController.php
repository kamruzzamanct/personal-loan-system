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
        $this->authorize('viewAny', LoanApplication::class);

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
        $this->authorize('export', LoanApplication::class);

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
        $search = $filters['search'];
        $riskLevel = $filters['risk_level'];
        $employmentType = $filters['employment_type'];

        if ($search !== '' || $employmentType !== '') {
            $this->authorize('filter', LoanApplication::class);
        }

        if (in_array($riskLevel, [RiskLevel::Low->value, RiskLevel::Medium->value], true)) {
            $this->authorize('filter', LoanApplication::class);
        }

        if (in_array($riskLevel, RiskLevel::highRiskValues(), true)) {
            $this->authorize('viewHighRisk', LoanApplication::class);
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

        if (in_array($riskLevel, array_map(static fn (RiskLevel $case): string => $case->value, RiskLevel::cases()), true)) {
            $query->where('risk_level', $riskLevel);
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
        $this->authorize('view', $loanApplication);

        $loanApplication->loadMissing('approvedByUser');

        return view('admin.loan-applications.show', [
            'loanApplication' => $loanApplication,
        ]);
    }

    public function approve(Request $request, LoanApplication $loanApplication): RedirectResponse
    {
        return $this->updateStatus(
            request: $request,
            loanApplication: $loanApplication,
            nextStatus: LoanApplicationStatus::Approved,
        );
    }

    public function markUnderReview(Request $request, LoanApplication $loanApplication): RedirectResponse
    {
        return $this->updateStatus(
            request: $request,
            loanApplication: $loanApplication,
            nextStatus: LoanApplicationStatus::UnderReview,
        );
    }

    public function decline(Request $request, LoanApplication $loanApplication): RedirectResponse
    {
        return $this->updateStatus(
            request: $request,
            loanApplication: $loanApplication,
            nextStatus: LoanApplicationStatus::Declined,
        );
    }

    private function updateStatus(
        Request $request,
        LoanApplication $loanApplication,
        LoanApplicationStatus $nextStatus,
    ): RedirectResponse
    {
        $this->authorize('approve', $loanApplication);

        $status = $loanApplication->status instanceof LoanApplicationStatus
            ? $loanApplication->status->value
            : (string) $loanApplication->status;

        if ($status === $nextStatus->value) {
            $message = $nextStatus === LoanApplicationStatus::Approved
                ? 'Loan application is already approved.'
                : "Loan application is already marked as {$this->statusLabel($nextStatus)}.";

            return redirect()
                ->route('admin.loan-applications.show', $loanApplication)
                ->with('success', $message);
        }

        if (
            $status === LoanApplicationStatus::Approved->value
            && $nextStatus !== LoanApplicationStatus::Approved
        ) {
            return redirect()
                ->route('admin.loan-applications.show', $loanApplication)
                ->withErrors([
                    'status' => 'Approved applications cannot be moved to another status.',
                ]);
        }

        $payload = [
            'status' => $nextStatus->value,
        ];

        if ($nextStatus === LoanApplicationStatus::Approved) {
            $payload['approved_at'] = now();
            $payload['approved_by_user_id'] = $request->user()?->id;
        } else {
            $payload['approved_at'] = null;
            $payload['approved_by_user_id'] = null;
        }

        $loanApplication->update($payload);

        if ($nextStatus === LoanApplicationStatus::Approved) {
            SendLoanApprovedNotificationJob::dispatch($loanApplication->fresh());

            return redirect()
                ->route('admin.loan-applications.show', $loanApplication)
                ->with('success', 'Loan application approved successfully. Applicant notification is queued.');
        }

        return redirect()
            ->route('admin.loan-applications.show', $loanApplication)
            ->with('success', 'Loan application status updated to '.$this->statusLabel($nextStatus).'.');
    }

    private function statusLabel(LoanApplicationStatus $status): string
    {
        return match ($status) {
            LoanApplicationStatus::Pending => 'pending',
            LoanApplicationStatus::UnderReview => 'under review',
            LoanApplicationStatus::Approved => 'approved',
            LoanApplicationStatus::Declined => 'declined',
        };
    }
}
