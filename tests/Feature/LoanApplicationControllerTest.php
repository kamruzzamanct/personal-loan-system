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
            'phone' => '1234567890',
            'loan_amount' => 10000,
            'employment_type' => 'salaried',
            'monthly_income' => 6000,
            'consent' => '1',
        ]);

        $response->assertRedirect(route('loan-applications.create'));
        $response->assertSessionHas('success', 'Loan application submitted successfully.');

        $this->assertDatabaseHas('loan_applications', [
            'email' => 'john@example.com',
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
            'phone' => '1234567890',
            'loan_amount' => 30000,
            'employment_type' => 'self-employed',
            'monthly_income' => 10000,
            'consent' => '1',
        ]);

        $response->assertRedirect(route('loan-applications.create'));
        $response->assertSessionHas('success', 'Loan application submitted successfully.');

        $this->assertDatabaseHas('loan_applications', [
            'email' => 'jane@example.com',
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
            'phone' => '1234567890',
            'loan_amount' => 12000,
            'employment_type' => 'salaried',
            'monthly_income' => 7000,
            'consent' => true,
        ]);

        $response->assertCreated();
        $response->assertJson([
            'message' => 'Loan application submitted successfully.',
            'data' => [
                'email' => 'alex@example.com',
            ],
        ]);
    }
}
