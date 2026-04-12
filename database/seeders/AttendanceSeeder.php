<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::all();
        $now = Carbon::now();

        // Seed attendance for current month (up to today)
        $startOfMonth = $now->copy()->startOfMonth();
        $today = $now->copy();

        $current = $startOfMonth->copy();

        while ($current->lte($today)) {
            // Skip weekends
            if ($current->isWeekday()) {
                foreach ($employees as $employee) {
                    // 90% chance of being present
                    $status = rand(1, 100) <= 90 ? 'present' : 'absent';

                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $current->toDateString(),
                        'status' => $status,
                    ]);
                }
            }

            $current->addDay();
        }

        // Also seed previous month for payroll demo
        $prevMonth = $now->copy()->subMonth();
        $startPrev = $prevMonth->copy()->startOfMonth();
        $endPrev = $prevMonth->copy()->endOfMonth();

        $current = $startPrev->copy();

        while ($current->lte($endPrev)) {
            if ($current->isWeekday()) {
                foreach ($employees as $employee) {
                    $status = rand(1, 100) <= 88 ? 'present' : 'absent';

                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $current->toDateString(),
                        'status' => $status,
                    ]);
                }
            }

            $current->addDay();
        }
    }
}
