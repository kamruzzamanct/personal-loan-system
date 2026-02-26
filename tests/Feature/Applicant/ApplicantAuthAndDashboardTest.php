<?php

declare(strict_types=1);

namespace Tests\Feature\Applicant;

use App\Enums\AdminRole;
use App\Enums\RiskLevel;
use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicantAuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_applicant_login_from_applicant_dashboard(): void
    {
        $response = $this->get(route('applicant.dashboard'));

        $response->assertRedirect(route('applicant.login'));
    }

    public function test_applicant_can_register_and_is_redirected_to_dashboard(): void
    {
        $response = $this->post(route('applicant.register.store'), [
            'name' => 'John Applicant',
            'email' => 'john@applicant.test',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('applicant.dashboard'));
        $response->assertSessionHas('success', 'Registration completed successfully.');

        $user = User::query()->where('email', 'john@applicant.test')->firstOrFail();
        $this->assertSame(AdminRole::Customer, $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_applicant_can_login_and_view_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@login.test',
            'password' => Hash::make('secret1234'),
            'role' => AdminRole::Customer->value,
        ]);

        $response = $this->post(route('applicant.login.store'), [
            'email' => 'customer@login.test',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect(route('applicant.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_from_applicant_login_redirects_to_admin_dashboard(): void
    {
        $adminUser = User::factory()->create([
            'email' => 'admin@login.test',
            'password' => Hash::make('secret1234'),
            'role' => AdminRole::SuperAdmin->value,
        ]);

        $response = $this->post(route('applicant.login.store'), [
            'email' => 'admin@login.test',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($adminUser, 'admin');
        $this->assertGuest('web');
    }

    public function test_authenticated_admin_can_open_applicant_login_page(): void
    {
        $adminUser = User::factory()->create([
            'role' => AdminRole::SuperAdmin->value,
        ]);

        $response = $this->actingAs($adminUser, 'admin')->get(route('applicant.login'));

        $response->assertOk();
        $response->assertSeeText('Applicant Login');
    }

    public function test_dashboard_shows_only_current_applicant_applications(): void
    {
        $user = User::factory()->create([
            'email' => 'owner@applicant.test',
            'role' => AdminRole::Customer->value,
        ]);

        $this->actingAs($user);

        $ownedApplication = LoanApplication::factory()->create([
            'user_id' => $user->id,
            'email' => 'owner@applicant.test',
            'risk_level' => RiskLevel::High->value,
        ]);

        $ownedByEmailOnlyApplication = LoanApplication::factory()->create([
            'user_id' => null,
            'email' => 'owner@applicant.test',
            'risk_level' => RiskLevel::Low->value,
        ]);

        LoanApplication::factory()->create([
            'email' => 'other@applicant.test',
        ]);

        $response = $this->get(route('applicant.dashboard'));

        $response->assertOk();
        $response->assertSeeText('My Loan Applications');
        $response->assertSeeText('#'.$ownedApplication->id);
        $response->assertSeeText('#'.$ownedByEmailOnlyApplication->id);
        $response->assertViewHas('loanApplications', static function ($loanApplications): bool {
            return $loanApplications->total() === 2;
        });
    }

    public function test_applicant_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'role' => AdminRole::Customer->value,
        ]);

        $response = $this->actingAs($user)->post(route('applicant.logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
