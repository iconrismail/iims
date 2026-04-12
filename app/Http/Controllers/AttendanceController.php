<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Http\Requests\StoreAttendanceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $date = $request->input('date', Carbon::today()->toDateString());
        $month = $request->input('month', Carbon::today()->month);
        $year = $request->input('year', Carbon::today()->year);

        if ($user->isAdmin()) {
            $attQuery = Attendance::with('employee.user')
                ->whereMonth('date', $month)
                ->whereYear('date', $year);

            if ($request->filled('employee_id')) {
                $attQuery->where('employee_id', $request->employee_id);
            }
            if ($request->filled('status_filter')) {
                $attQuery->where('status', $request->status_filter);
            }
            if ($request->filled('date_from')) {
                $attQuery->whereDate('date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $attQuery->whereDate('date', '<=', $request->date_to);
            }

            $attendances = $attQuery->orderByDesc('date')->paginate(20)->withQueryString();

            $employees = Employee::with('user')
                ->where('status', 'active')
                ->orderBy('employee_id')
                ->get();

            return view('attendance.index', compact('attendances', 'employees', 'date', 'month', 'year'));
        }

        // Employee: view only their own attendance
        $employee = $user->employee;
        $attendances = $employee
            ? Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderByDesc('date')
                ->paginate(20)
            : collect();

        return view('attendance.employee', compact('attendances', 'month', 'year'));
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        // Check for duplicate entry
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Attendance already recorded for this employee on this date.')
                ->withInput();
        }

        $data = $request->validated();

        // Auto-compute late_minutes if not explicitly provided
        if (empty($data['late_minutes']) && !empty($data['shift']) && !empty($data['time_in'])) {
            $data['late_minutes'] = Attendance::computeLateMinutes($data['shift'], $data['time_in']);
        }

        // If late_minutes > 0 and status is 'present', auto-upgrade to 'late'
        if (($data['late_minutes'] ?? 0) > 0 && $data['status'] === 'present') {
            $data['status'] = 'late';
        }

        Attendance::create($data);

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance recorded successfully.');
    }

    /**
     * Bulk attendance: mark all employees for a given date.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => ['required', 'date'],
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', 'in:present,absent,late,half_day'],
        ]);

        $date = $request->input('date');

        foreach ($request->input('attendance') as $employeeId => $status) {
            Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'date' => $date],
                ['status' => $status]
            );
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Bulk attendance recorded successfully.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance record deleted.');
    }
}
