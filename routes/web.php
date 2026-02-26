<?php

use App\Http\Controllers\LoanApplicationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/loan-applications/create', [LoanApplicationController::class, 'create'])
    ->name('loan-applications.create');

Route::post('/loan-applications', [LoanApplicationController::class, 'store'])
    ->name('loan-applications.store');
