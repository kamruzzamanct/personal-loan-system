<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AdminRole;
use App\Enums\EmploymentType;
use App\Enums\LoanApplicationStatus;
use App\Enums\RiskLevel;
use App\Jobs\SendLoanApprovedNotificationJob;
use App\Models\LoanApplication;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LoanApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_index_displays_paginated_results_ordered_by_latest(): void
    {
        $this->actingAsAdmin();

        LoanApplication::factory()->create([
            'email' => 'oldest@example.com',
            'created_at' => now()->subDays(3),
        ]);

        LoanApplication::factory()->count(14)->create([
            'created_at' => now()->subDay(),
        ]);

        LoanApplication::factory()->create([
            'email' => 'newest@example.com',
            'created_at' => now(),
        ]);

        $response = $this->get(route('admin.loan-applications.index'));

        $response->assertOk();
        $response->assertDontSee('oldest@example.com');
        $response->assertViewHas('loanApplications', function (LengthAwarePaginator $loanApplications): bool {
            $items = $loanApplications->items();

            return $loanApplications->total() === 16
                && $loanApplications->perPage() === 15
                && count($items) === 15
                && $items[0]->email === 'newest@example.com';
        });
    }

    public function test_index_supports_search_by_name_or_email(): void
    {
        $this->actingAsAdmin();

        LoanApplication::factory()->create([
            'first_name' => 'Unique',
            'last_name' => 'Applicant',
            'email' => 'unique@applicant.test',
        ]);

        LoanApplication::factory()->create([
            'first_name' => 'Other',
            'last_name' => 'Person',
            'email' => 'other@person.test',
        ]);

        $response = $this->get(route('admin.loan-applications.index', [
            'search' => 'Unique',
        ]));

        $response->assertOk();
        $response->assertSee('unique@applicant.test');
        $response->assertDontSee('other@person.test');
        $response->assertViewHas('loanApplications', function (LengthAwarePaginator $loanApplications): bool {
            return $loanApplications->total() === 1;
        });
    }

    public function test_index_filters_by_risk_level(): void
    {
        $this->actingAsAdmin();

        LoanApplication::factory()->create([
            'email' => 'high-risk@example.com',
            'risk_level' => RiskLevel::High->value,
        ]);

        LoanApplication::factory()->create([
            'email' => 'low-risk@example.com',
            'risk_level' => RiskLevel::Low->value,
        ]);

        $response = $this->get(route('admin.loan-applications.index', [
            'risk_level' => RiskLevel::High->value,
        ]));

        $response->assertOk();
        $response->assertSee('high-risk@example.com');
        $response->assertDontSee('low-risk@example.com');
        $response->assertViewHas('loanApplications', function (LengthAwarePaginator $loanApplications): bool {
            return $loanApplications->total() === 1;
        });
    }

    public function test_index_filters_by_employment_type(): void
    {
        $this->actingAsAdmin();

        LoanApplication::factory()->create([
            'email' => 'self-employed@example.com',
            'employment_type' => EmploymentType::SelfEmployed->value,
            'is_self_employed' => true,
        ]);

        LoanApplication::factory()->create([
            'email' => 'salaried@example.com',
            'employment_type' => EmploymentType::Salaried->value,
            'is_self_employed' => false,
        ]);

        $response = $this->get(route('admin.loan-applications.index', [
            'employment_type' => EmploymentType::SelfEmployed->value,
        ]));

        $response->assertOk();
        $response->assertSee('self-employed@example.com');
        $response->assertDontSee('salaried@example.com');
        $response->assertViewHas('loanApplications', function (LengthAwarePaginator $loanApplications): bool {
            return $loanApplications->total() === 1;
        });
    }

    public function test_index_pagination_keeps_filter_query_string(): void
    {
        $this->actingAsAdmin();

        LoanApplication::factory()->count(16)->create([
            'first_name' => 'Searchable',
            'risk_level' => RiskLevel::High->value,
        ]);

        $response = $this->get(route('admin.loan-applications.index', [
            'search' => 'Searchable',
            'risk_level' => RiskLevel::High->value,
        ]));

        $response->assertOk();
        $response->assertSee('search=Searchable', false);
        $response->assertSee('risk_level=high', false);
        $response->assertSee('page=2', false);
    }

    public function test_show_displays_single_application_details(): void
    {
        $this->actingAsAdmin();

        $loanApplication = LoanApplication::factory()->create([
            'email' => 'single-details@example.com',
        ]);

        $response = $this->get(route('admin.loan-applications.show', $loanApplication));

        $response->assertOk();
        $response->assertSee('Loan Application Details');
        $response->assertSee('single-details@example.com');
    }

    public function test_approve_updates_application_and_dispatches_customer_notification_job(): void
    {
        Queue::fake();
        $approver = $this->actingAsRiskManager();

        $loanApplication = LoanApplication::factory()->create([
            'status' => LoanApplicationStatus::Pending->value,
            'approved_at' => null,
            'approved_by_user_id' => null,
        ]);

        $response = $this->post(route('admin.loan-applications.approve', $loanApplication));

        $response->assertRedirect(route('admin.loan-applications.show', $loanApplication));
        $response->assertSessionHas('success', 'Loan application approved successfully. Applicant notification is queued.');

        $loanApplication->refresh();

        $this->assertSame(LoanApplicationStatus::Approved, $loanApplication->status);
        $this->assertNotNull($loanApplication->approved_at);
        $this->assertSame($approver->id, $loanApplication->approved_by_user_id);

        Queue::assertPushed(SendLoanApprovedNotificationJob::class, function (SendLoanApprovedNotificationJob $job) use ($loanApplication): bool {
            return $job->loanApplication->id === $loanApplication->id;
        });
    }

    public function test_approve_returns_forbidden_for_viewer_without_permission(): void
    {
        $this->actingAsViewer();

        $loanApplication = LoanApplication::factory()->create([
            'status' => LoanApplicationStatus::Pending->value,
        ]);

        $response = $this->post(route('admin.loan-applications.approve', $loanApplication));

        $response->assertForbidden();
    }

    public function test_approve_does_not_dispatch_job_for_already_approved_application(): void
    {
        Queue::fake();
        $this->actingAsAdmin();

        $loanApplication = LoanApplication::factory()->create([
            'status' => LoanApplicationStatus::Approved->value,
            'approved_at' => now()->subDay(),
        ]);

        $response = $this->post(route('admin.loan-applications.approve', $loanApplication));

        $response->assertRedirect(route('admin.loan-applications.show', $loanApplication));
        $response->assertSessionHas('success', 'Loan application is already approved.');
        Queue::assertNotPushed(SendLoanApprovedNotificationJob::class);
    }

    private function actingAsAdmin(): User
    {
        $user = User::factory()->create([
            'role' => AdminRole::SuperAdmin->value,
        ]);
        $user->assignRole('Super Admin');

        $this->actingAs($user);

        return $user;
    }

    private function actingAsRiskManager(): User
    {
        $user = User::factory()->create([
            'role' => AdminRole::RiskManager->value,
        ]);
        $user->assignRole('Risk Manager');

        $this->actingAs($user);

        return $user;
    }

    private function actingAsViewer(): User
    {
        $user = User::factory()->create([
            'role' => AdminRole::Viewer->value,
        ]);
        $user->assignRole('Viewer');

        $this->actingAs($user);

        return $user;
    }
}
