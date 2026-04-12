<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Payslip;
use App\Http\Requests\ProcessPayrollRequest;
use App\Jobs\ProcessPayrollJob;
use App\Services\PayrollService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(
        private PayrollService $payrollService
    ) {}

    /**
     * List all payroll periods (Admin).
     */
    public function index(Request $request): View
    {
        $query = Payroll::withCount('payslips')
            ->latest('year')
            ->latest('month');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $payrolls = $query->paginate(12)->withQueryString();
        $pendingJobs = \DB::table('jobs')->count();

        return view('payroll.index', compact('payrolls', 'pendingJobs'));
    }

    /**
     * Show the form to process a new payroll (Admin).
     */
    public function create(): View
    {
        return view('payroll.create');
    }

    /**
     * Process payroll for a given month/year — dispatches to queue (Admin).
     */
    public function process(ProcessPayrollRequest $request): RedirectResponse
    {
        $month = (int) $request->month;
        $year  = (int) $request->year;

        ProcessPayrollJob::dispatch($month, $year);

        $label = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');

        return redirect()->route('payroll.index')
            ->with('success', "Payroll job for {$label} has been queued. Run `php artisan queue:work` to process.");
    }

    /**
     * Show a payroll period with all payslips (Admin).
     */
    public function show(Payroll $payroll): View
    {
        $payroll->load('payslips.employee.user', 'payslips.employee.department', 'payslips.anomalies');

        return view('payroll.show', compact('payroll'));
    }

    /**
     * Mark payroll as paid (Admin).
     */
    public function markPaid(Payroll $payroll): RedirectResponse
    {
        $payroll->update(['status' => 'paid']);

        return back()->with('success', 'Payroll marked as paid.');
    }

    /**
     * List payslips for the logged-in employee.
     */
    public function myPayslips(Request $request): View
    {
        $employee = $request->user()->employee;

        $payslips = $employee
            ? Payslip::where('employee_id', $employee->id)
                ->with('payroll')
                ->latest()
                ->paginate(12)
            : collect();

        return view('payslips.index', compact('payslips'));
    }

    /**
     * Show a single payslip detail.
     */
    public function showPayslip(Payslip $payslip, Request $request): View
    {
        $user = $request->user();

        // Authorization: admin can view any, employee can only view own
        if (!$user->isAdmin()) {
            if (!$user->employee || $payslip->employee_id !== $user->employee->id) {
                abort(403);
            }
        }

        $payslip->load('payroll', 'employee.user', 'employee.department');

        return view('payslips.show', compact('payslip'));
    }

    /**
     * Download a payslip as PDF.
     */
    public function downloadPayslip(Payslip $payslip, Request $request): \Illuminate\Http\Response
    {
        $user = $request->user();
        if (!$user->isAdmin()) {
            if (!$user->employee || $payslip->employee_id !== $user->employee->id) {
                abort(403);
            }
        }
        $payslip->load('payroll', 'employee.user', 'employee.department');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payslips.pdf', compact('payslip'));
        return $pdf->download("payslip-{$payslip->employee->employee_id}-{$payslip->payroll->periodLabel()}.pdf");
    }
}
