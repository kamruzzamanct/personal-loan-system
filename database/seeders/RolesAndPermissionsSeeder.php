<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'view applications',
            'filter applications',
            'view high-risk only',
            'approve applications',
            'export reports',
            'manage users',
        ];

        /** @var Collection<string, Permission> $permissions */
        $permissions = collect($permissionNames)
            ->mapWithKeys(static fn (string $permissionName): array => [
                $permissionName => Permission::findOrCreate($permissionName, 'web'),
            ]);

        $superAdminRole = Role::findOrCreate('Super Admin', 'web');
        $riskManagerRole = Role::findOrCreate('Risk Manager', 'web');
        $viewerRole = Role::findOrCreate('Viewer', 'web');

        $superAdminRole->syncPermissions($permissions->values()->all());
        $riskManagerRole->syncPermissions($permissions->only([
            'view applications',
            'filter applications',
            'view high-risk only',
            'approve applications',
            'export reports',
        ])->values()->all());
        $viewerRole->syncPermissions($permissions->only([
            'view applications',
        ])->values()->all());

        $superAdminName = (string) env('SUPER_ADMIN_NAME', 'Super Admin');
        $superAdminEmail = (string) env('SUPER_ADMIN_EMAIL', 'superadmin@company.com');
        $superAdminPassword = (string) env('SUPER_ADMIN_PASSWORD', 'password12345');

        $superAdminUser = User::query()->firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'name' => $superAdminName,
                'password' => $superAdminPassword,
                'role' => AdminRole::SuperAdmin->value,
            ],
        );

        $currentRoleValue = $superAdminUser->role instanceof AdminRole
            ? $superAdminUser->role->value
            : (string) $superAdminUser->role;

        if ($currentRoleValue !== AdminRole::SuperAdmin->value) {
            $superAdminUser->forceFill([
                'role' => AdminRole::SuperAdmin->value,
            ])->save();
        }

        $superAdminUser->syncRoles(['Super Admin']);

        User::query()->each(function (User $user): void {
            $roleValue = $user->role instanceof AdminRole ? $user->role->value : (string) $user->role;

            $spatieRole = match ($roleValue) {
                AdminRole::SuperAdmin->value => 'Super Admin',
                AdminRole::RiskManager->value => 'Risk Manager',
                AdminRole::Viewer->value => 'Viewer',
                default => null,
            };

            if ($spatieRole !== null) {
                $user->syncRoles([$spatieRole]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
