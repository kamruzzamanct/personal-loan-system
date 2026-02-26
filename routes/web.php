<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LoanApplicationController as AdminLoanApplicationController;
use App\Http\Controllers\LoanApplicationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/loan-applications/create', [LoanApplicationController::class, 'create'])
    ->name('loan-applications.create');

Route::post('/loan-applications', [LoanApplicationController::class, 'store'])
    ->name('loan-applications.store');

Route::prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::middleware('guest')->group(function (): void {
            Route::get('/login', [AdminAuthController::class, 'create'])->name('login');
            Route::post('/login', [AdminAuthController::class, 'store'])->name('login.store');
        });

        Route::middleware(['auth', 'admin.role'])->group(function (): void {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            Route::get('/loan-applications', [AdminLoanApplicationController::class, 'index'])
                ->name('loan-applications.index');
            Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        });
    });
