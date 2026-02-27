<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RiskLevel;
use App\Jobs\SendHighRiskLoanNotificationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LoanApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_is_accessible(): void
    {
        $response = $this->get(route('loan-applications.create'));

        $response->assertOk();
        $response->assertSeeText('Personal Loan Application');
    }

    public function test_store_persists_low_risk_application_and_does_not_dispatch_notification_job(): void
    {
        Queue::fake();

        $response = $this->post(route('loan-applications.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'age' => 32,
            'phone' => '1234567890',
            'address' => '123 Main Street, Springfield',
            'loan_amount' => 10000,
            'employment_type' => 'salaried',
            'designation' => 'Software Engineer',
            'company_name' => 'Tech Corp',
            'monthly_income' => 6000,
            'loan_proposal' => 'Need funds for home renovation and emergency reserve.',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('loan-applications.create'));
        $response->assertSessionHas('success', 'Loan application submitted successfully.');

        $this->assertDatabaseHas('loan_applications', [
            'email' => 'john@example.com',
            'age' => 32,
            'address' => '123 Main Street, Springfield',
            'designation' => 'Software Engineer',
            'company_name' => 'Tech Corp',
            'living_description' => null,
            'loan_proposal' => 'Need funds for home renovation and emergency reserve.',
            'risk_level' => RiskLevel::Low->value,
            'is_self_employed' => false,
        ]);

        Queue::assertNotPushed(SendHighRiskLoanNotificationJob::class);
    }

    public function test_store_persists_high_risk_application_and_dispatches_notification_job(): void
    {
        Queue::fake();

        $response = $this->post(route('loan-applications.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'age' => 29,
            'phone' => '1234567890',
            'address' => '45 Lake View Avenue, Austin',
            'loan_amount' => 30000,
            'employment_type' => 'self-employed',
            'living_description' => 'I run a freelance digital design business.',
            'monthly_income' => 10000,
            'loan_proposal' => 'Working capital support for seasonal cash flow gap.',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('loan-applications.create'));
        $response->assertSessionHas('success', 'Loan application submitted successfully.');

        $this->assertDatabaseHas('loan_applications', [
            'email' => 'jane@example.com',
            'age' => 29,
            'address' => '45 Lake View Avenue, Austin',
            'designation' => null,
            'company_name' => null,
            'living_description' => 'I run a freelance digital design business.',
            'loan_proposal' => 'Working capital support for seasonal cash flow gap.',
            'employment_type' => 'self_employed',
            'risk_level' => RiskLevel::High->value,
            'is_self_employed' => true,
        ]);

        Queue::assertPushed(SendHighRiskLoanNotificationJob::class, 1);
    }

    public function test_store_returns_json_created_response_for_json_requests(): void
    {
        Queue::fake();

        $response = $this->postJson(route('loan-applications.store'), [
            'first_name' => 'Alex',
            'last_name' => 'Taylor',
            'email' => 'alex@example.com',
            'age' => 34,
            'phone' => '1234567890',
            'address' => '909 Sunset Boulevard, Miami',
            'loan_amount' => 12000,
            'employment_type' => 'salaried',
            'designation' => 'Operations Analyst',
            'company_name' => 'Northline Services',
            'monthly_income' => 7000,
            'loan_proposal' => 'Debt consolidation with lower monthly payment.',
            'consent' => true,
        ]);

        $response->assertCreated();
        $response->assertJson([
            'message' => 'Loan application submitted successfully.',
            'data' => [
                'email' => 'alex@example.com',
                'age' => 34,
                'address' => '909 Sunset Boulevard, Miami',
                'designation' => 'Operations Analyst',
                'company_name' => 'Northline Services',
                'living_description' => null,
                'loan_proposal' => 'Debt consolidation with lower monthly payment.',
            ],
        ]);
    }

    public function test_store_fails_when_age_is_below_minimum_limit(): void
    {
        $response = $this->from(route('loan-applications.create'))->post(route('loan-applications.store'), [
            'first_name' => 'Young',
            'last_name' => 'Applicant',
            'email' => 'young@example.com',
            'age' => 19,
            'phone' => '1234567890',
            'address' => '14 Midtown Lane, Chicago',
            'loan_amount' => 6000,
            'employment_type' => 'salaried',
            'designation' => 'Associate',
            'company_name' => 'Starter Inc',
            'monthly_income' => 4500,
            'loan_proposal' => 'Small emergency fund support.',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('loan-applications.create'));
        $response->assertSessionHasErrors([
            'age' => 'You need to be at least 20 years old to get the loan.',
        ]);
    }

    public function test_store_requires_salaried_fields_when_employment_type_is_salaried(): void
    {
        $response = $this->from(route('loan-applications.create'))->post(route('loan-applications.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john-role@example.com',
            'age' => 30,
            'phone' => '1234567890',
            'address' => '77 Riverside Drive, Seattle',
            'loan_amount' => 9000,
            'employment_type' => 'salaried',
            'monthly_income' => 7000,
            'loan_proposal' => 'Medical expense coverage.',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('loan-applications.create'));
        $response->assertSessionHasErrors(['designation', 'company_name']);
    }

    public function test_store_requires_living_description_for_self_employed_applicants(): void
    {
        $response = $this->from(route('loan-applications.create'))->post(route('loan-applications.store'), [
            'first_name' => 'Mila',
            'last_name' => 'Khan',
            'email' => 'mila-self@example.com',
            'age' => 31,
            'phone' => '1234567890',
            'address' => '99 Harbor Street, Boston',
            'loan_amount' => 15000,
            'employment_type' => 'self-employed',
            'monthly_income' => 9000,
            'loan_proposal' => 'Seasonal inventory and operations support.',
            'consent' => '1',
        ]);

        $response->assertRedirect(route('loan-applications.create'));
        $response->assertSessionHasErrors(['living_description']);
    }
}
