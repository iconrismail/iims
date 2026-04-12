<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $this->employee = User::create([
            'name' => 'Employee', 'email' => 'emp@test.com',
            'password' => bcrypt('password'), 'role' => 'employee',
        ]);
    }

    // ── Admin-only routes ─────────────────────────────────

    /** @test */
    public function employee_cannot_access_employees_index(): void
    {
        $this->actingAs($this->employee)->get(route('employees.index'))->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_employees_index(): void
    {
        $this->actingAs($this->admin)->get(route('employees.index'))->assertStatus(200);
    }

    /** @test */
    public function employee_cannot_access_departments(): void
    {
        $this->actingAs($this->employee)->get(route('departments.index'))->assertStatus(403);
    }

    /** @test */
    public function employee_cannot_access_reports(): void
    {
        $this->actingAs($this->employee)->get(route('reports.index'))->assertStatus(403);
    }

    /** @test */
    public function employee_cannot_access_audit_log(): void
    {
        $this->actingAs($this->employee)->get(route('audit.index'))->assertStatus(403);
    }

    /** @test */
    public function employee_cannot_access_tax_settings(): void
    {
        $this->actingAs($this->employee)->get(route('tax.index'))->assertStatus(403);
    }

    /** @test */
    public function employee_cannot_access_queue_monitor(): void
    {
        $this->actingAs($this->employee)->get(route('queue.monitor'))->assertStatus(403);
    }

    // ── Shared routes ─────────────────────────────────────

    /** @test */
    public function both_roles_can_access_attendance(): void
    {
        $dept = Department::create(['name' => 'Eng', 'description' => null]);
        Employee::create([
            'user_id' => $this->employee->id, 'department_id' => $dept->id,
            'employee_id' => 'EMP-0001', 'position' => 'Dev',
            'hire_date' => '2024-01-01', 'basic_salary' => 3000,
            'allowances' => 0, 'deductions' => 0, 'status' => 'active',
        ]);

        $this->actingAs($this->admin)->get(route('attendance.index'))->assertStatus(200);
        $this->actingAs($this->employee)->get(route('attendance.index'))->assertStatus(200);
    }

    /** @test */
    public function both_roles_can_access_leave_index(): void
    {
        $this->actingAs($this->admin)->get(route('leaves.index'))->assertStatus(200);
        $this->actingAs($this->employee)->get(route('leaves.index'))->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $adminRoutes = [
            route('employees.index'),
            route('departments.index'),
            route('payroll.index'),
            route('reports.index'),
            route('audit.index'),
        ];

        foreach ($adminRoutes as $route) {
            $this->get($route)->assertRedirect(route('login'));
        }
    }

    // ── Login ─────────────────────────────────────────────

    /** @test */
    public function valid_credentials_log_user_in(): void
    {
        $this->post(route('login'), [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($this->admin);
    }

    /** @test */
    public function invalid_credentials_return_error(): void
    {
        $this->post(route('login'), [
            'email'    => 'admin@test.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /** @test */
    public function logout_redirects_to_login(): void
    {
        $this->actingAs($this->admin)
             ->post(route('logout'))
             ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
