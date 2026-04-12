<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Services\ChurnRiskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Employee::with('user', 'department')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) =>
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                  );
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees   = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create(): View
    {
        $departments = Department::orderBy('name')->get();

        return view('employees.create', compact('departments'));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $createdUser = null;

        DB::transaction(function () use ($request, &$createdUser) {
            // Create user account
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Str::random(32), // throwaway — employee sets their own via reset link
                'role'     => 'employee',
            ]);
            $createdUser = $user;

            // Generate employee ID
            $lastEmployee = Employee::orderBy('id', 'desc')->first();
            $nextId = $lastEmployee ? ((int) substr($lastEmployee->employee_id, 4)) + 1 : 1;
            $employeeId = 'EMP-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Create employee record
            Employee::create([
                'user_id'            => $user->id,
                'department_id'      => $request->department_id,
                'employee_id'        => $employeeId,
                'position'           => $request->position,
                'phone'              => $request->phone,
                'address'            => $request->address,
                'date_of_birth'      => $request->date_of_birth,
                'hire_date'          => $request->hire_date,
                'basic_salary'       => $request->basic_salary,
                'allowances'         => $request->allowances ?? 0,
                'deductions'         => $request->deductions ?? 0,
                'status'             => $request->status,
                'contract_type'      => $request->contract_type ?? 'permanent',
                'contract_end_date'  => $request->contract_end_date ?: null,
                'probation_end_date' => $request->probation_end_date ?: null,
            ]);
        });

        if ($createdUser) {
            try {
                /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
                $broker = Password::broker();
                $token = $broker->createToken($createdUser);
                $resetUrl = route('password.reset', ['token' => $token, 'email' => $createdUser->email]);
                \Illuminate\Support\Facades\Mail::to($createdUser->email)->send(new \App\Mail\WelcomeEmployee($createdUser, $resetUrl));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Welcome email failed for user ' . $createdUser->id . ': ' . $e->getMessage());
            }
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee, ChurnRiskService $churnRisk): View
    {
        $employee->load('user', 'department', 'payslips.payroll', 'documents');

        $churn = $churnRisk->score($employee);

        return view('employees.show', compact('employee', 'churn'));
    }

    public function edit(Employee $employee): View
    {
        $employee->load('user');
        $departments = Department::orderBy('name')->get();

        return view('employees.edit', compact('employee', 'departments'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($request, $employee) {
            // Update user account
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = $request->password;
            }

            $employee->user->update($userData);

            // Update employee record
            $employee->update([
                'department_id'      => $request->department_id,
                'position'           => $request->position,
                'phone'              => $request->phone,
                'address'            => $request->address,
                'date_of_birth'      => $request->date_of_birth,
                'hire_date'          => $request->hire_date,
                'basic_salary'       => $request->basic_salary,
                'allowances'         => $request->allowances ?? 0,
                'deductions'         => $request->deductions ?? 0,
                'status'             => $request->status,
                'contract_type'      => $request->contract_type ?? 'permanent',
                'contract_end_date'  => $request->contract_end_date ?: null,
                'probation_end_date' => $request->probation_end_date ?: null,
            ]);
        });

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        // Delete the user (cascades to employee via FK)
        $employee->user->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
