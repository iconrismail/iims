<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;
    private Employee $empRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);

        $dept = Department::create(['name' => 'Engineering', 'description' => null]);
        $this->employee = User::create([
            'name' => 'Employee', 'email' => 'emp@test.com',
            'password' => bcrypt('password'), 'role' => 'employee',
        ]);
        $this->empRecord = Employee::create([
            'user_id' => $this->employee->id, 'department_id' => $dept->id,
            'employee_id' => 'EMP-0001', 'position' => 'Dev',
            'hire_date' => '2024-01-01', 'basic_salary' => 5000,
            'allowances' => 500, 'deductions' => 100, 'status' => 'active',
        ]);
    }

    /** @test */
    public function admin_can_view_payroll_index(): void
    {
        $this->actingAs($this->admin)
             ->get(route('payroll.index'))
             ->assertStatus(200);
    }

    /** @test */
    public function employee_cannot_access_payroll_index(): void
    {
        $this->actingAs($this->employee)
             ->get(route('payroll.index'))
             ->assertStatus(403);
    }

    /** @test */
    public function admin_can_process_payroll(): void
    {
        $this->actingAs($this->admin)
             ->post(route('payroll.process'), ['month' => 1, 'year' => 2024])
             ->assertRedirect(route('payroll.index'));

        $this->assertDatabaseHas('payrolls', ['month' => 1, 'year' => 2024]);
    }

    /** @test */
    public function admin_can_mark_payroll_as_paid(): void
    {
        $payroll = Payroll::create(['month' => 1, 'year' => 2024, 'status' => 'processed']);

        $this->actingAs($this->admin)
             ->patch(route('payroll.markPaid', $payroll))
             ->assertRedirect();

        $this->assertEquals('paid', $payroll->fresh()->status);
    }

    /** @test */
    public function employee_can_view_own_payslips(): void
    {
        $payroll = Payroll::create(['month' => 1, 'year' => 2024, 'status' => 'processed']);
        $payslip = Payslip::create([
            'payroll_id' => $payroll->id, 'employee_id' => $this->empRecord->id,
            'basic_salary' => 5000, 'allowances' => 500, 'deductions' => 100,
            'net_salary' => 5400, 'days_worked' => 22, 'days_absent' => 0,
        ]);

        $this->actingAs($this->employee)
             ->get(route('payslips.show', $payslip))
             ->assertStatus(200);
    }

    /** @test */
    public function employee_cannot_view_another_employees_payslip(): void
    {
        $other = User::create([
            'name' => 'Other', 'email' => 'other@test.com',
            'password' => bcrypt('password'), 'role' => 'employee',
        ]);
        $dept2 = Department::create(['name' => 'Finance', 'description' => null]);
        $otherEmp = Employee::create([
            'user_id' => $other->id, 'department_id' => $dept2->id,
            'employee_id' => 'EMP-0002', 'position' => 'Analyst',
            'hire_date' => '2024-01-01', 'basic_salary' => 4000,
            'allowances' => 400, 'deductions' => 80, 'status' => 'active',
        ]);

        $payroll = Payroll::create(['month' => 2, 'year' => 2024, 'status' => 'processed']);
        $payslip = Payslip::create([
            'payroll_id' => $payroll->id, 'employee_id' => $otherEmp->id,
            'basic_salary' => 4000, 'allowances' => 400, 'deductions' => 80,
            'net_salary' => 4320, 'days_worked' => 22, 'days_absent' => 0,
        ]);

        $this->actingAs($this->employee)
             ->get(route('payslips.show', $payslip))
             ->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_any_payslip(): void
    {
        $payroll = Payroll::create(['month' => 1, 'year' => 2024, 'status' => 'processed']);
        $payslip = Payslip::create([
            'payroll_id' => $payroll->id, 'employee_id' => $this->empRecord->id,
            'basic_salary' => 5000, 'allowances' => 500, 'deductions' => 100,
            'net_salary' => 5400, 'days_worked' => 22, 'days_absent' => 0,
        ]);

        $this->actingAs($this->admin)
             ->get(route('payslips.show', $payslip))
             ->assertStatus(200);
    }

    /** @test */
    public function guest_is_redirected_to_login(): void
    {
        $this->get(route('payroll.index'))->assertRedirect(route('login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
