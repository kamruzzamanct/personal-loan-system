<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SendLoanApprovedNotificationJob;
use App\Mail\LoanApprovedNotification;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendLoanApprovedNotificationJobTest extends TestCase
{
    public function test_job_sends_approved_email_to_applicant(): void
    {
        Mail::fake();

        $loanApplication = new LoanApplication([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'loan_amount' => 15000,
            'employment_type' => 'salaried',
            'monthly_income' => 9000,
            'consent' => true,
            'risk_level' => 'low',
            'is_self_employed' => false,
            'status' => 'approved',
        ]);

        (new SendLoanApprovedNotificationJob($loanApplication))->handle();

        Mail::assertSent(LoanApprovedNotification::class, function (LoanApprovedNotification $mail): bool {
            return $mail->hasTo('john@example.com')
                && $mail->loanApplication->email === 'john@example.com'
                && $mail->loanApplication->status->value === 'approved';
        });
    }

    public function test_approved_mailable_renders_application_details(): void
    {
        $loanApplication = new LoanApplication([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'loan_amount' => 15000,
            'employment_type' => 'salaried',
            'monthly_income' => 9000,
            'consent' => true,
            'risk_level' => 'low',
            'is_self_employed' => false,
            'status' => 'approved',
        ]);

        $html = (new LoanApprovedNotification($loanApplication))->render();

        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString('john@example.com', $html);
        $this->assertStringContainsString('APPROVED', $html);
        $this->assertStringContainsString('Salaried', $html);
    }
}
