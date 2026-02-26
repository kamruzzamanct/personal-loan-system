<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use Database\Seeders\RolesAndPermissionsSeeder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_login_page_is_accessible_for_guests(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertOk();
        $response->assertSeeText('Admin Login');
    }

    public function test_admin_user_is_redirected_to_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'role' => AdminRole::SuperAdmin->value,
        ]);
        $user->assignRole('Super Admin');

        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_non_admin_user_is_rejected_after_login_attempt(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('secret123'),
            'role' => AdminRole::Customer->value,
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => 'customer@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_routes_require_authentication(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_non_admin_user_gets_forbidden_for_admin_area(): void
    {
        $user = User::factory()->create([
            'role' => AdminRole::Customer->value,
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_user_can_logout_from_admin_area(): void
    {
        $user = User::factory()->create([
            'role' => AdminRole::RiskManager->value,
        ]);
        $user->assignRole('Risk Manager');

        $response = $this->actingAs($user)->post(route('admin.logout'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
