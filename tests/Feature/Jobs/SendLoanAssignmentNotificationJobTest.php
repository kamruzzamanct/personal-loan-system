<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Enums\AdminRole;
use App\Jobs\SendLoanAssignmentNotificationJob;
use App\Mail\LoanAssignedToRiskManagerNotification;
use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendLoanAssignmentNotificationJobTest extends TestCase
{
    public function test_job_sends_assignment_email_to_risk_manager(): void
    {
        Mail::fake();

        $riskManager = new User([
            'name' => 'Risk Manager One',
            'email' => 'risk.manager@example.com',
            'role' => AdminRole::RiskManager->value,
        ]);

        $loanApplication = new LoanApplication([
            'first_name' => 'Alex',
            'last_name' => 'Brown',
            'email' => 'alex@example.com',
            'loan_amount' => 15000,
            'monthly_income' => 9000,
            'employment_type' => 'salaried',
            'risk_level' => 'high',
        ]);

        (new SendLoanAssignmentNotificationJob($loanApplication, $riskManager))->handle();

        Mail::assertSent(LoanAssignedToRiskManagerNotification::class, function (LoanAssignedToRiskManagerNotification $mail): bool {
            return $mail->hasTo('risk.manager@example.com')
                && $mail->riskManager->email === 'risk.manager@example.com'
                && $mail->loanApplication->email === 'alex@example.com';
        });
    }

    public function test_assignment_mailable_renders_expected_details(): void
    {
        $riskManager = new User([
            'name' => 'Risk Manager One',
            'email' => 'risk.manager@example.com',
            'role' => AdminRole::RiskManager->value,
        ]);

        $loanApplication = new LoanApplication([
            'first_name' => 'Alex',
            'last_name' => 'Brown',
            'email' => 'alex@example.com',
            'loan_amount' => 15000,
            'monthly_income' => 9000,
            'employment_type' => 'salaried',
            'risk_level' => 'high',
        ]);

        $html = (new LoanAssignedToRiskManagerNotification($loanApplication, $riskManager))->render();

        $this->assertStringContainsString('Risk Manager One', $html);
        $this->assertStringContainsString('Alex Brown', $html);
        $this->assertStringContainsString('alex@example.com', $html);
        $this->assertStringContainsString('HIGH', $html);
    }
}
