<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SendHighRiskLoanNotificationJob;
use App\Mail\HighRiskLoanNotification;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendHighRiskLoanNotificationJobTest extends TestCase
{
    public function test_job_sends_high_risk_email_to_admin(): void
    {
        Mail::fake();

        $loanApplication = new LoanApplication([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '1234567890',
            'loan_amount' => 30000,
            'employment_type' => 'self_employed',
            'monthly_income' => 10000,
            'consent' => true,
            'risk_level' => 'high',
            'is_self_employed' => true,
        ]);

        (new SendHighRiskLoanNotificationJob($loanApplication))->handle();

        Mail::assertSent(HighRiskLoanNotification::class, function (HighRiskLoanNotification $mail): bool {
            return $mail->hasTo('admin@company.com')
                && $mail->loanApplication->email === 'jane@example.com'
                && $mail->loanApplication->risk_level->value === 'high';
        });
    }

    public function test_high_risk_mailable_renders_applicant_details(): void
    {
        $loanApplication = new LoanApplication([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone' => '1234567890',
            'loan_amount' => 30000,
            'employment_type' => 'self_employed',
            'monthly_income' => 10000,
            'consent' => true,
            'risk_level' => 'high',
            'is_self_employed' => true,
        ]);

        $html = (new HighRiskLoanNotification($loanApplication))->render();

        $this->assertStringContainsString('Jane Smith', $html);
        $this->assertStringContainsString('jane@example.com', $html);
        $this->assertStringContainsString('HIGH', $html);
        $this->assertStringContainsString('Self Employed', $html);
    }
}
