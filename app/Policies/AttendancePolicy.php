<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Admin can view all attendance; employees can only view their own.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->employee && $attendance->employee_id === $user->employee->id;
    }

    /**
     * Only admins can manage (create/update/delete) attendance.
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
