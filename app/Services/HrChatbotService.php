<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payslip;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\PerformanceReview;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HrChatbotService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->model  = config('services.anthropic.model', 'claude-haiku-4-5-20251001');
    }

    /**
     * Send a message and get a response, grounded in the authenticated user's own data.
     *
     * @param  User   $user     The authenticated user asking the question
     * @param  string $message  The user's message
     * @param  array  $history  Prior turns [['role'=>'user'|'assistant','content'=>'...']]
     * @return string
     */
    public function chat(User $user, string $message, array $history = []): string
    {
        if (empty($this->apiKey)) {
            return 'The HR Assistant is not configured yet. Please contact your system administrator.';
        }

        $context = $this->buildContext($user);
        $systemPrompt = $this->buildSystemPrompt($user, $context);

        $messages = [];
        foreach ($history as $turn) {
            if (in_array($turn['role'] ?? '', ['user', 'assistant']) && isset($turn['content'])) {
                $messages[] = ['role' => $turn['role'], 'content' => (string) $turn['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model'      => $this->model,
                'max_tokens' => 512,
                'system'     => $systemPrompt,
                'messages'   => $messages,
            ]);

            if ($response->successful()) {
                return $response->json('content.0.text', 'Sorry, I could not generate a response.');
            }

            Log::error('HR Chatbot API error: ' . $response->status() . ' ' . $response->body());
            return 'I encountered an error processing your request. Please try again.';
        } catch (\Exception $e) {
            Log::error('HR Chatbot exception: ' . $e->getMessage());
            return 'I am currently unavailable. Please try again later.';
        }
    }

    // ── Context builders ─────────────────────────────────────────────────────

    private function buildSystemPrompt(User $user, array $ctx): string
    {
        $role = ucfirst($user->role);
        $name = $user->name;

        $lines = [
            "You are an HR Assistant for IIMS Payroll & HR System. You are speaking with {$name} ({$role}).",
            "Answer questions about their own payroll, attendance, leave, and performance data provided below.",
            "Be concise, friendly, and accurate. Never fabricate data. If data is not available, say so.",
            "Do NOT discuss other employees' private data. Do NOT execute system commands.",
            "Currency used is NLE (Sierra Leone Leone).",
            "",
            "=== EMPLOYEE DATA ===",
        ];

        foreach ($ctx as $section => $content) {
            $lines[] = "-- {$section} --";
            $lines[] = $content;
        }

        return implode("\n", $lines);
    }

    private function buildContext(User $user): array
    {
        $ctx = [];

        $employee = $user->employee;
        if (!$employee) {
            $ctx['Profile'] = "Role: {$user->role}. No employee profile linked.";
            return $ctx;
        }

        // Profile
        $ctx['Profile'] = implode(', ', array_filter([
            "Employee ID: {$employee->employee_id}",
            "Position: {$employee->position}",
            "Department: " . ($employee->department?->name ?? 'N/A'),
            "Hire Date: " . ($employee->hire_date?->format('M d, Y') ?? 'N/A'),
            "Status: {$employee->status}",
            "Basic Salary: NLE " . number_format((float)$employee->basic_salary, 2),
            "Allowances: NLE " . number_format((float)$employee->allowances, 2),
            "Deductions: NLE " . number_format((float)$employee->deductions, 2),
        ]));

        // Last 3 payslips
        $payslips = Payslip::where('employee_id', $employee->id)
            ->with('payroll')
            ->latest('id')
            ->limit(3)
            ->get();

        if ($payslips->isNotEmpty()) {
            $lines = [];
            foreach ($payslips as $slip) {
                $period = $slip->payroll?->periodLabel() ?? 'Unknown';
                $lines[] = "{$period}: Net NLE " . number_format((float)$slip->net_salary, 2)
                    . ", Days Worked: {$slip->days_worked}, Status: {$slip->payroll?->status}";
            }
            $ctx['Recent Payslips'] = implode(' | ', $lines);
        } else {
            $ctx['Recent Payslips'] = 'No payslips on record.';
        }

        // Attendance this month
        $month = Carbon::now()->month;
        $year  = Carbon::now()->year;
        $present = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->where('status', 'present')->count();
        $absent = Attendance::where('employee_id', $employee->id)
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->where('status', 'absent')->count();
        $ctx['Attendance This Month'] = "Present: {$present} days, Absent: {$absent} days.";

        // Leave requests (last 6 months)
        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->where('start_date', '>=', Carbon::now()->subMonths(6))
            ->with('leaveType')
            ->latest('start_date')
            ->limit(5)
            ->get();

        if ($leaves->isNotEmpty()) {
            $ctx['Recent Leave Requests'] = implode(' | ', $leaves->map(fn($l) =>
                ($l->leaveType?->name ?? 'Leave')
                . " ({$l->start_date->format('M d')}–{$l->end_date->format('M d')})"
                . ": {$l->total_days} days, {$l->status}"
            )->toArray());
        } else {
            $ctx['Recent Leave Requests'] = 'No leave requests in the last 6 months.';
        }

        // Latest performance review
        $review = PerformanceReview::where('employee_id', $employee->id)
            ->whereNotNull('overall_score')
            ->latest('period_year')
            ->first();

        if ($review) {
            $ctx['Latest Performance Review'] = "Period: {$review->review_period} {$review->period_year}"
                . ", Score: {$review->overall_score}/100"
                . ", Status: {$review->status}"
                . ($review->comments ? ", Comments: {$review->comments}" : '');
        } else {
            $ctx['Latest Performance Review'] = 'No performance reviews on record.';
        }

        return $ctx;
    }
}
