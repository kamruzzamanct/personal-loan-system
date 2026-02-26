<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LoanApplicationController as AdminLoanApplicationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Applicant\DashboardController as ApplicantDashboardController;
use App\Http\Controllers\Auth\ApplicantAuthController;
use App\Http\Controllers\LoanApplicationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest:web')->group(function (): void {
    Route::get('/login', [ApplicantAuthController::class, 'createLogin'])->name('applicant.login');
    Route::post('/login', [ApplicantAuthController::class, 'storeLogin'])->name('applicant.login.store');
    Route::get('/register', [ApplicantAuthController::class, 'createRegister'])->name('applicant.register');
    Route::post('/register', [ApplicantAuthController::class, 'storeRegister'])->name('applicant.register.store');
});

Route::get('/loan-applications/create', [LoanApplicationController::class, 'create'])
    ->name('loan-applications.create');

Route::post('/loan-applications', [LoanApplicationController::class, 'store'])
    ->name('loan-applications.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/applicant/dashboard', [ApplicantDashboardController::class, 'index'])->name('applicant.dashboard');
    Route::post('/logout', [ApplicantAuthController::class, 'destroy'])->name('applicant.logout');
});

Route::prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::middleware('guest:admin')->group(function (): void {
            Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
            Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
        });

        Route::middleware(['auth:admin', 'admin.role'])->group(function (): void {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::get('/loan-applications', [AdminLoanApplicationController::class, 'index'])
                ->middleware('permission:view applications')
                ->name('loan-applications.index');
            Route::get('/loan-applications/export/{format}', [AdminLoanApplicationController::class, 'export'])
                ->whereIn('format', ['csv', 'xlsx'])
                ->middleware('permission:export reports')
                ->name('loan-applications.export');
            Route::get('/loan-applications/{loanApplication}', [AdminLoanApplicationController::class, 'show'])
                ->middleware('permission:view applications')
                ->name('loan-applications.show');
            Route::post('/loan-applications/{loanApplication}/approve', [AdminLoanApplicationController::class, 'approve'])
                ->middleware('permission:approve applications')
                ->name('loan-applications.approve');
            Route::resource('/users', AdminUserController::class)
                ->except(['show'])
                ->middleware('permission:manage users');
            Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        });
    });
