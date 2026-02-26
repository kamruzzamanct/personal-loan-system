<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoanApprovedNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public LoanApplication $loanApplication)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Loan Application Has Been Approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.loan-approved-notification',
            with: [
                'loanApplication' => $this->loanApplication,
            ],
        );
    }
}
