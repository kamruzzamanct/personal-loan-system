<?php

namespace App\Providers;

use App\Models\LoanApplication;
use App\Policies\LoanApplicationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(LoanApplication::class, LoanApplicationPolicy::class);
    }
}
