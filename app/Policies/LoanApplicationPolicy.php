<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LoanApplication;
use App\Models\User;

class LoanApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAdminRole()
            && $user->can('view applications');
    }

    public function view(User $user, LoanApplication $loanApplication): bool
    {
        return $this->viewAny($user);
    }

    public function export(User $user): bool
    {
        return $user->hasAdminRole()
            && $user->can('export reports');
    }

    public function approve(User $user, LoanApplication $loanApplication): bool
    {
        return $user->hasAdminRole()
            && $user->can('approve applications');
    }

    public function filter(User $user): bool
    {
        return $user->hasAdminRole()
            && $user->can('filter applications');
    }

    public function viewHighRisk(User $user): bool
    {
        return $user->hasAdminRole()
            && $user->canAny(['filter applications', 'view high-risk only']);
    }
}
