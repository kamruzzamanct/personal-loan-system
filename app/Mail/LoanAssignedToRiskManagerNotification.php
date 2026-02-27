<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoanAssignedToRiskManagerNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public LoanApplication $loanApplication,
        public User $riskManager,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Loan Application Assigned to You',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-assigned-to-risk-manager-notification',
            with: [
                'loanApplication' => $this->loanApplication,
                'riskManager' => $this->riskManager,
            ],
        );
    }
}
