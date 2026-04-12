<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Only admins can view the employee list.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Admin can view any employee. Employee can view their own record.
     */
    public function view(User $user, Employee $employee): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->employee && $user->employee->id === $employee->id;
    }

    /**
     * Only admins can create/update/delete employees.
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
