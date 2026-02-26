<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HighRiskLoanNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public LoanApplication $loanApplication)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'High Risk Loan Application Alert',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.high-risk-loan-notification',
            with: [
                'loanApplication' => $this->loanApplication,
            ],
        );
    }
}
