<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\QueueMonitorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PerformanceReviewController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\PublicHolidayController;
use App\Http\Controllers\ProfileUpdateController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes (no auth required) ────────────────────
Route::get('/offline', fn () => view('offline'))->name('offline');

// ─── Guest Routes ────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // 2FA challenge (user not yet fully authenticated)
    Route::get('/2fa/challenge', [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/2fa/challenge', [TwoFactorController::class, 'verifyChallenge'])->name('2fa.verify');

    // Password reset (new employees set their password via emailed link)
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'show'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
});

// ─── Authenticated Routes ────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export-pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.exportPdf')->middleware('role:admin');

    // ── Admin Only Routes ────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        // Employees CRUD
        Route::resource('employees', EmployeeController::class);

        // Departments CRUD
        Route::resource('departments', DepartmentController::class)->except('show');

        // Attendance management
        Route::post('/attendance/bulk', [AttendanceController::class, 'bulkStore'])->name('attendance.bulk');
        Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

        // Payroll management
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
        Route::post('/payroll/process', [PayrollController::class, 'process'])->name('payroll.process');
        Route::get('/payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
        Route::patch('/payroll/{payroll}/paid', [PayrollController::class, 'markPaid'])->name('payroll.markPaid');

        // Leave approve/reject moved to admin+manager group below

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/payroll', [ReportController::class, 'payrollSummary'])->name('payroll');
            Route::get('/attendance', [ReportController::class, 'attendanceSummary'])->name('attendance');
            Route::get('/export/payroll', [ReportController::class, 'exportPayroll'])->name('export.payroll');
            Route::get('/export/attendance', [ReportController::class, 'exportAttendance'])->name('export.attendance');
            Route::get('/export/employees', [ReportController::class, 'exportEmployees'])->name('export.employees');
        });

        // Audit log
        Route::get('/audit-log', [AuditController::class, 'index'])->name('audit.index');

        // Tax & Deduction Rules
        Route::get('/tax', [TaxController::class, 'index'])->name('tax.index');
        Route::post('/tax/brackets', [TaxController::class, 'storeBracket'])->name('tax.brackets.store');
        Route::delete('/tax/brackets/{bracket}', [TaxController::class, 'destroyBracket'])->name('tax.brackets.destroy');
        Route::post('/tax/rules', [TaxController::class, 'storeRule'])->name('tax.rules.store');
        Route::patch('/tax/rules/{rule}/toggle', [TaxController::class, 'toggleRule'])->name('tax.rules.toggle');
        Route::delete('/tax/rules/{rule}', [TaxController::class, 'destroyRule'])->name('tax.rules.destroy');

        // 2FA setup (admin only)
        Route::get('/2fa/setup', [TwoFactorController::class, 'showSetup'])->name('2fa.setup');
        Route::post('/2fa/enable', [TwoFactorController::class, 'enableTwoFactor'])->name('2fa.enable');
        Route::post('/2fa/disable', [TwoFactorController::class, 'disableTwoFactor'])->name('2fa.disable');

        // Queue Monitor
        Route::get('/queue-monitor', [QueueMonitorController::class, 'index'])->name('queue.monitor');
        Route::post('/queue-monitor/retry', [QueueMonitorController::class, 'retryFailed'])->name('queue.retryFailed');
        Route::post('/queue-monitor/clear', [QueueMonitorController::class, 'clearFailed'])->name('queue.clearFailed');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Overtime — admin-only write operations
        Route::get('/overtime/create', [OvertimeController::class, 'create'])->name('overtime.create');
        Route::post('/overtime', [OvertimeController::class, 'store'])->name('overtime.store');
        Route::delete('/overtime/{overtime}', [OvertimeController::class, 'destroy'])->name('overtime.destroy');

        // Bonus management
        Route::get('/bonuses', [BonusController::class, 'index'])->name('bonuses.index');
        Route::get('/bonuses/create', [BonusController::class, 'create'])->name('bonuses.create');
        Route::post('/bonuses', [BonusController::class, 'store'])->name('bonuses.store');
        Route::post('/bonuses/{bonus}/approve', [BonusController::class, 'approve'])->name('bonuses.approve');
        Route::delete('/bonuses/{bonus}', [BonusController::class, 'destroy'])->name('bonuses.destroy');

        // Document management
        Route::get('/employees/{employee}/documents', [DocumentController::class, 'index'])->name('employees.documents');
        Route::post('/employees/{employee}/documents', [DocumentController::class, 'store'])->name('employees.documents.store');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        // Performance reviews (admin-only actions)
        Route::get('/performance/{review}/ai-summary', [PerformanceReviewController::class, 'aiSummary'])->name('performance.aiSummary');
        Route::get('/performance/kpi', [PerformanceReviewController::class, 'kpiIndex'])->name('performance.kpi');

        // Public Holidays
        Route::get('/holidays', [PublicHolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [PublicHolidayController::class, 'store'])->name('holidays.store');
        Route::delete('/holidays/{holiday}', [PublicHolidayController::class, 'destroy'])->name('holidays.destroy');

        // Profile update requests — admin review
        Route::get('/profile-updates', [ProfileUpdateController::class, 'index'])->name('profile-updates.index');
        Route::post('/profile-updates/{profileRequest}/approve', [ProfileUpdateController::class, 'approve'])->name('profile-updates.approve');
        Route::post('/profile-updates/{profileRequest}/reject', [ProfileUpdateController::class, 'reject'])->name('profile-updates.reject');

        // Analytics reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/analytics', [ReportController::class, 'analytics'])->name('analytics');
        });
    });

    // ── Admin + Manager Routes ────────────────────────────
    Route::middleware('role:admin,manager')->group(function () {
        // Leave approval
        Route::post('/leaves/{leave}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');

        // Overtime view + approval
        Route::get('/overtime', [OvertimeController::class, 'index'])->name('overtime.index');
        Route::post('/overtime/{overtime}/approve', [OvertimeController::class, 'approve'])->name('overtime.approve');
        Route::post('/overtime/{overtime}/reject', [OvertimeController::class, 'reject'])->name('overtime.reject');

        // Performance reviews — create / edit / submit (managers limited to their dept)
        Route::get('/performance/create', [PerformanceReviewController::class, 'create'])->name('performance.create');
        Route::post('/performance', [PerformanceReviewController::class, 'store'])->name('performance.store');
        Route::post('/performance/{review}/submit', [PerformanceReviewController::class, 'submit'])->name('performance.submit');
    });

    // ── Shared Routes (Admin + Employee) ─────────────────
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/my-payslips', [PayrollController::class, 'myPayslips'])->name('payslips.index');
    Route::get('/payslips/{payslip}', [PayrollController::class, 'showPayslip'])->name('payslips.show');
    Route::get('/payslips/{payslip}/download', [PayrollController::class, 'downloadPayslip'])->name('payslips.download');

    // Leave requests (shared: employees submit, admins can view all)
    Route::resource('leaves', LeaveController::class)->only(['index', 'create', 'store', 'show']);

    // Notifications (shared)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Performance reviews (shared: admin + employee)
    Route::get('/performance', [PerformanceReviewController::class, 'index'])->name('performance.index');
    Route::get('/performance/{review}', [PerformanceReviewController::class, 'show'])->name('performance.show');
    Route::post('/performance/{review}/acknowledge', [PerformanceReviewController::class, 'acknowledge'])->name('performance.acknowledge');

    // Employee self-service: profile update request
    Route::get('/profile-updates/create', [ProfileUpdateController::class, 'create'])->name('profile-updates.create');
    Route::post('/profile-updates', [ProfileUpdateController::class, 'store'])->name('profile-updates.store');

    // HR Chatbot (all authenticated users)
    Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot.chat');
});
