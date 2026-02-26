<?php

declare(strict_types=1);

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('applicant.login');
        }

        if ($user->hasAdminRole()) {
            return redirect()->route('admin.dashboard');
        }

        $loanApplications = LoanApplication::query()
            ->where(function ($query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($orQuery) use ($user): void {
                        $orQuery->whereNull('user_id')
                            ->where('email', $user->email);
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('applicant.dashboard', [
            'user' => $user,
            'loanApplications' => $loanApplications,
        ]);
    }
}
