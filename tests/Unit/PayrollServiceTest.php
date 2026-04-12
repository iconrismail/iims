<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $service;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PayrollService();

        $dept = Department::create(['name' => 'Engineering', 'description' => null]);
        $user = User::create([
            'name'     => 'Test Employee',
            'email'    => 'emp@test.com',
            'password' => bcrypt('password'),
            'role'     => 'employee',
        ]);
        $this->employee = Employee::create([
            'user_id'       => $user->id,
            'department_id' => $dept->id,
            'employee_id'   => 'EMP-0001',
            'position'      => 'Developer',
            'hire_date'     => '2023-01-01',
            'basic_salary'  => 5000.00,
            'allowances'    => 1000.00,
            'deductions'    => 200.00,
            'status'        => 'active',
        ]);
    }

    /** @test */
    public function it_calculates_full_salary_when_no_attendance_records(): void
    {
        $result = $this->service->calculateSalary($this->employee, 1, 2024);

        $this->assertEquals(5000.00, $result['basic_salary']);
        $this->assertEquals(1000.00, $result['allowances']);
        $this->assertGreaterThanOrEqual(0, $result['net_salary']);
    }

    /** @test */
    public function it_prorates_salary_based_on_attendance(): void
    {
        $month = 1;
        $year  = 2024;

        // Mark present for first 5 weekdays only
        $date = Carbon::create($year, $month, 1);
        $added = 0;
        while ($added < 5) {
            if ($date->isWeekday()) {
                Attendance::create([
                    'employee_id' => $this->employee->id,
                    'date'        => $date->toDateString(),
                    'status'      => 'present',
                ]);
                $added++;
            }
            $date->addDay();
        }

        $result = $this->service->calculateSalary($this->employee, $month, $year);

        $this->assertLessThan(5000.00, $result['basic_salary']);
        $this->assertEquals(5, $result['days_worked']);
        $this->assertGreaterThanOrEqual(0, $result['net_salary']);
    }

    /** @test */
    public function net_salary_is_never_negative(): void
    {
        // Give employee very high deductions
        $this->employee->update(['deductions' => 999999.00]);

        $result = $this->service->calculateSalary($this->employee, 1, 2024);

        $this->assertEquals(0, $result['net_salary']);
    }

    /** @test */
    public function it_counts_days_worked_and_absent_correctly(): void
    {
        $month = 3;
        $year  = 2025;

        Attendance::create(['employee_id' => $this->employee->id, 'date' => '2025-03-03', 'status' => 'present']);
        Attendance::create(['employee_id' => $this->employee->id, 'date' => '2025-03-04', 'status' => 'absent']);
        Attendance::create(['employee_id' => $this->employee->id, 'date' => '2025-03-05', 'status' => 'present']);

        $result = $this->service->calculateSalary($this->employee, $month, $year);

        $this->assertEquals(2, $result['days_worked']);
        $this->assertEquals(1, $result['days_absent']);
    }

    /** @test */
    public function it_processes_payroll_for_all_active_employees(): void
    {
        $payroll = $this->service->processPayroll(1, 2024);

        $this->assertNotNull($payroll);
        $this->assertEquals('processed', $payroll->status);
        $this->assertCount(1, $payroll->payslips);
    }

    /** @test */
    public function it_skips_inactive_employees_during_payroll(): void
    {
        $this->employee->update(['status' => 'inactive']);

        $payroll = $this->service->processPayroll(2, 2024);

        $this->assertCount(0, $payroll->payslips);
    }

    /** @test */
    public function it_re_processes_payroll_and_replaces_old_payslips(): void
    {
        $this->service->processPayroll(1, 2024);
        $payroll = $this->service->processPayroll(1, 2024);

        $this->assertCount(1, $payroll->payslips);
    }
}
