<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\HighRiskLoanNotification;
use App\Models\LoanApplication;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendHighRiskLoanNotificationJob implements ShouldQueue
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
        Mail::to('admin@company.com')
            ->send(new HighRiskLoanNotification($this->loanApplication));
    }
}
