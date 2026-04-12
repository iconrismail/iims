<?php

namespace App\Policies;

use App\Models\Payslip;
use App\Models\User;

class PayslipPolicy
{
    /**
     * Admin can view any payslip; employees can only view their own.
     */
    public function view(User $user, Payslip $payslip): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->employee && $payslip->employee_id === $user->employee->id;
    }

    /**
     * Only admins can manage payslips.
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
