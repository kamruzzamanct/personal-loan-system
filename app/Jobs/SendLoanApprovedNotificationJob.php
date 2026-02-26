<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\LoanApprovedNotification;
use App\Models\LoanApplication;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLoanApprovedNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public LoanApplication $loanApplication)
    {
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        Mail::to($this->loanApplication->email)
            ->send(new LoanApprovedNotification($this->loanApplication));
    }
}
