<?php

declare(strict_types=1);

namespace App\Enums;

enum LoanApplicationStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Declined = 'declined';
}
