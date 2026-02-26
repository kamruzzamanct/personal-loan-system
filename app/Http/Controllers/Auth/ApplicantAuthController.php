<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ApplicantLoginRequest;
use App\Http\Requests\Auth\ApplicantRegisterRequest;
use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApplicantAuthController extends Controller
{
    public function createLogin(): View
    {
        return view('auth.login');
    }

    public function storeLogin(ApplicantLoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'These credentials do not match our records.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user && $user->hasAdminRole()) {
            Auth::guard('web')->logout();

            if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
                return redirect()->route('admin.login')
                    ->withErrors([
                        'email' => 'Unable to sign in to admin area. Please try again.',
                    ]);
            }

            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        $this->linkOrphanApplicationsToUser($user);

        return redirect()->intended(route('applicant.dashboard'));
    }

    public function createRegister(): View
    {
        return view('auth.register');
    }

    public function storeRegister(ApplicantRegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => AdminRole::Customer->value,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $this->linkOrphanApplicationsToUser($user);

        return redirect()
            ->route('applicant.dashboard')
            ->with('success', 'Registration completed successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->regenerate();

        return redirect()
            ->route('home')
            ->with('success', 'You have been logged out successfully.');
    }

    private function linkOrphanApplicationsToUser(?User $user): void
    {
        if (! $user) {
            return;
        }

        LoanApplication::query()
            ->whereNull('user_id')
            ->where('email', $user->email)
            ->update([
                'user_id' => $user->id,
            ]);
    }
}
