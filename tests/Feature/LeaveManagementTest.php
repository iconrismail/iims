<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employeeUser;
    private Employee $employee;
    private LeaveType $leaveType;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);

        $dept = Department::create(['name' => 'HR', 'description' => null]);
        $this->employeeUser = User::create([
            'name' => 'Employee', 'email' => 'emp@test.com',
            'password' => bcrypt('password'), 'role' => 'employee',
        ]);
        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id, 'department_id' => $dept->id,
            'employee_id' => 'EMP-0001', 'position' => 'Officer',
            'hire_date' => '2024-01-01', 'basic_salary' => 3000,
            'allowances' => 0, 'deductions' => 0, 'status' => 'active',
        ]);

        $this->leaveType = LeaveType::create([
            'name' => 'Annual Leave', 'days_per_year' => 21, 'is_paid' => true,
        ]);
    }

    /** @test */
    public function employee_can_submit_leave_request(): void
    {
        $this->actingAs($this->employeeUser)
             ->post(route('leaves.store'), [
                 'leave_type_id' => $this->leaveType->id,
                 'start_date'    => now()->addDays(5)->toDateString(),
                 'end_date'      => now()->addDays(7)->toDateString(),
                 'reason'        => 'Family vacation',
             ])
             ->assertRedirect(route('leaves.index'));

        $this->assertDatabaseHas('leave_requests', [
            'employee_id'   => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'status'        => 'pending',
        ]);
    }

    /** @test */
    public function admin_can_approve_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id, 'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'total_days' => 3, 'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
             ->post(route('leaves.approve', $leave))
             ->assertRedirect();

        $this->assertEquals('approved', $leave->fresh()->status);
        $this->assertEquals($this->admin->id, $leave->fresh()->approved_by);
    }

    /** @test */
    public function admin_can_reject_leave_request(): void
    {
        $leave = LeaveRequest::create([
            'employee_id' => $this->employee->id, 'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'total_days' => 3, 'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
             ->post(route('leaves.reject', $leave), ['admin_note' => 'Busy season'])
             ->assertRedirect();

        $this->assertEquals('rejected', $leave->fresh()->status);
        $this->assertEquals('Busy season', $leave->fresh()->admin_note);
    }

    /** @test */
    public function leave_request_calculates_total_days(): void
    {
        // Find next Monday from today
        $monday = now()->next('Monday');
        $friday = $monday->copy()->addDays(4); // Mon+4 = Friday

        $this->actingAs($this->employeeUser)
             ->post(route('leaves.store'), [
                 'leave_type_id' => $this->leaveType->id,
                 'start_date'    => $monday->toDateString(),
                 'end_date'      => $friday->toDateString(),
                 'reason'        => 'Test',
             ]);

        $leave = LeaveRequest::latest()->first();
        $this->assertNotNull($leave);
        $this->assertEquals(5, $leave->total_days);
    }
}
