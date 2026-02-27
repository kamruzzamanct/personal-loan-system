<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\LoanAssignedToRiskManagerNotification;
use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLoanAssignmentNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public LoanApplication $loanApplication,
        public User $riskManager,
    ) {
        $this->onQueue('mail');
    }

    public function handle(): void
    {
        Mail::to($this->riskManager->email)
            ->send(new LoanAssignedToRiskManagerNotification($this->loanApplication, $this->riskManager));
    }
}
