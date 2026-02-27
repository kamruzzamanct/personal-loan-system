<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Http\Requests\StoreLoanApplicationRequest;
use App\Jobs\SendHighRiskLoanNotificationJob;
use App\Models\LoanApplication;
use App\Services\LoanRiskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LoanApplicationController extends Controller
{
    public function create(): View
    {
        return view('loan-applications.create');
    }

    public function store(
        StoreLoanApplicationRequest $request,
        LoanRiskService $loanRiskService,
    ): JsonResponse|RedirectResponse {
        $validated = $request->validated();
        $user = $request->user();
        $userId = null;
        $employmentType = (string) $validated['employment_type'];

        if ($user && strcasecmp((string) $user->email, (string) $validated['email']) === 0) {
            $userId = $user->id;
        }

        if ($employmentType === EmploymentType::Salaried->value) {
            $validated['living_description'] = null;
        } else {
            $validated['designation'] = null;
            $validated['company_name'] = null;
        }

        $riskLevel = $loanRiskService->calculateRisk(
            (float) $validated['loan_amount'],
            (float) $validated['monthly_income'],
        );

        $loanApplication = LoanApplication::query()->create([
            ...$validated,
            'user_id' => $userId,
            'risk_level' => $riskLevel,
            'is_self_employed' => $loanRiskService->isSelfEmployed($validated['employment_type']),
        ]);

        if ($loanRiskService->isHighRisk($riskLevel)) {
            SendHighRiskLoanNotificationJob::dispatch($loanApplication);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Loan application submitted successfully.',
                'data' => $loanApplication->fresh(),
            ], 201);
        }

        return redirect()
            ->route('loan-applications.create')
            ->with('success', 'Loan application submitted successfully.');
    }
}
