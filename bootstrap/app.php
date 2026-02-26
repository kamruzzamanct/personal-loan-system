<?php

use App\Http\Middleware\EnsureAdminRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.role' => EnsureAdminRole::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(static function (Request $request): string {
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            return route('applicant.login');
        });

        $middleware->redirectUsersTo(static function (Request $request): string {
            $adminUser = auth('admin')->user();
            if (
                $adminUser
                && method_exists($adminUser, 'hasAdminRole')
                && $adminUser->hasAdminRole()
                && $request->is('admin/*')
            ) {
                return route('admin.dashboard');
            }

            $webUser = auth('web')->user();
            if ($webUser && method_exists($webUser, 'hasAdminRole') && $webUser->hasAdminRole()) {
                return route('admin.dashboard');
            }

            if ($webUser) {
                return route('applicant.dashboard');
            }

            return route('home');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
