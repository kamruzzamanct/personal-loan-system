<?php

use App\Http\Middleware\EnsureAdminRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.role' => EnsureAdminRole::class,
        ]);

        $middleware->redirectGuestsTo(static function (Request $request): string {
            return route('admin.login');
        });

        $middleware->redirectUsersTo(static function (Request $request): string {
            $user = $request->user();

            if ($user && method_exists($user, 'hasAdminRole') && $user->hasAdminRole()) {
                return route('admin.dashboard');
            }

            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
