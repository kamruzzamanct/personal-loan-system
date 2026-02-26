<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_manage_users_index_is_accessible_for_super_admin(): void
    {
        $this->actingAsSuperAdmin();

        User::factory()->create([
            'name' => 'Managed User',
            'email' => 'managed@example.com',
            'role' => AdminRole::Viewer->value,
        ]);

        $response = $this->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSeeText('Manage Users');
        $response->assertSeeText('managed@example.com');
    }

    public function test_manage_users_routes_are_forbidden_without_permission(): void
    {
        $this->actingAsViewer();

        $response = $this->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_store_creates_user_and_assigns_selected_role(): void
    {
        $this->actingAsSuperAdmin();

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Risk User',
            'email' => 'risk.user@example.com',
            'role' => AdminRole::RiskManager->value,
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'User created successfully.');

        $user = User::query()->where('email', 'risk.user@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(AdminRole::RiskManager, $user->role);
        $this->assertTrue($user->hasRole('Risk Manager'));
    }

    public function test_update_changes_role_and_password(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create([
            'email' => 'viewer.user@example.com',
            'role' => AdminRole::Viewer->value,
            'password' => 'oldpassword',
        ]);
        $user->syncRoles(['Viewer']);

        $response = $this->put(route('admin.users.update', $user), [
            'name' => 'Updated User',
            'email' => 'viewer.user@example.com',
            'role' => AdminRole::RiskManager->value,
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'User updated successfully.');

        $user->refresh();

        $this->assertSame(AdminRole::RiskManager, $user->role);
        $this->assertTrue($user->hasRole('Risk Manager'));
        $this->assertFalse($user->hasRole('Viewer'));
        $this->assertTrue(Hash::check('newsecret123', $user->password));
    }

    public function test_update_prevents_demoting_last_super_admin(): void
    {
        $superAdmin = User::query()
            ->where('role', AdminRole::SuperAdmin->value)
            ->firstOrFail();
        $superAdmin->syncRoles(['Super Admin']);
        $this->actingAs($superAdmin, 'admin');

        $response = $this->from(route('admin.users.edit', $superAdmin))
            ->put(route('admin.users.update', $superAdmin), [
                'name' => $superAdmin->name,
                'email' => $superAdmin->email,
                'role' => AdminRole::Viewer->value,
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertRedirect(route('admin.users.edit', $superAdmin));
        $response->assertSessionHasErrors('role');

        $superAdmin->refresh();
        $this->assertSame(AdminRole::SuperAdmin, $superAdmin->role);
    }

    public function test_destroy_deletes_user(): void
    {
        $this->actingAsSuperAdmin();

        $user = User::factory()->create([
            'role' => AdminRole::Viewer->value,
        ]);
        $user->syncRoles(['Viewer']);

        $response = $this->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'User deleted successfully.');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_destroy_prevents_self_deletion(): void
    {
        $superAdmin = $this->actingAsSuperAdmin();

        $response = $this->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $superAdmin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
        ]);
    }

    private function actingAsSuperAdmin(): User
    {
        $user = User::factory()->create([
            'role' => AdminRole::SuperAdmin->value,
        ]);
        $user->assignRole('Super Admin');

        $this->actingAs($user, 'admin');

        return $user;
    }

    private function actingAsViewer(): User
    {
        $user = User::factory()->create([
            'role' => AdminRole::Viewer->value,
        ]);
        $user->assignRole('Viewer');

        $this->actingAs($user, 'admin');

        return $user;
    }
}
