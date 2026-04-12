<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ────────────────────────────────────
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@iims.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // ── Demo Employees ────────────────────────────────
        $departments = Department::all();

        $employees = [
            [
                'name' => 'Ahmed Hassan',
                'email' => 'ahmed@iims.com',
                'position' => 'Software Engineer',
                'department' => 'Engineering',
                'basic_salary' => 5000.00,
                'allowances' => 800.00,
                'deductions' => 200.00,
                'phone' => '+232-61-1234567',
                'hire_date' => '2024-01-15',
            ],
            [
                'name' => 'Fatima Ali',
                'email' => 'fatima@iims.com',
                'position' => 'HR Manager',
                'department' => 'Human Resources',
                'basic_salary' => 4500.00,
                'allowances' => 600.00,
                'deductions' => 150.00,
                'phone' => '+232-61-2345678',
                'hire_date' => '2023-06-01',
            ],
            [
                'name' => 'Omar Ibrahim',
                'email' => 'omar@iims.com',
                'position' => 'Financial Analyst',
                'department' => 'Finance',
                'basic_salary' => 4800.00,
                'allowances' => 700.00,
                'deductions' => 180.00,
                'phone' => '+232-61-3456789',
                'hire_date' => '2024-03-10',
            ],
            [
                'name' => 'Amina Mohamed',
                'email' => 'amina@iims.com',
                'position' => 'Operations Coordinator',
                'department' => 'Operations',
                'basic_salary' => 3800.00,
                'allowances' => 500.00,
                'deductions' => 120.00,
                'phone' => '+232-61-4567890',
                'hire_date' => '2024-05-20',
            ],
            [
                'name' => 'Yusuf Abdi',
                'email' => 'yusuf@iims.com',
                'position' => 'Marketing Specialist',
                'department' => 'Marketing',
                'basic_salary' => 4200.00,
                'allowances' => 550.00,
                'deductions' => 160.00,
                'phone' => '+232-61-5678901',
                'hire_date' => '2023-11-01',
            ],
            [
                'name' => 'Hodan Ismail',
                'email' => 'hodan@iims.com',
                'position' => 'Backend Developer',
                'department' => 'Engineering',
                'basic_salary' => 5200.00,
                'allowances' => 850.00,
                'deductions' => 210.00,
                'phone' => '+232-61-6789012',
                'hire_date' => '2024-02-01',
            ],
            [
                'name' => 'Abdulahi Nur',
                'email' => 'abdulahi@iims.com',
                'position' => 'Accountant',
                'department' => 'Finance',
                'basic_salary' => 4000.00,
                'allowances' => 450.00,
                'deductions' => 130.00,
                'phone' => '+232-61-7890123',
                'hire_date' => '2024-07-15',
            ],
            [
                'name' => 'Sahra Warsame',
                'email' => 'sahra@iims.com',
                'position' => 'Recruitment Officer',
                'department' => 'Human Resources',
                'basic_salary' => 3600.00,
                'allowances' => 400.00,
                'deductions' => 100.00,
                'phone' => '+232-61-8901234',
                'hire_date' => '2025-01-10',
            ],
        ];

        foreach ($employees as $idx => $emp) {
            $user = User::create([
                'name' => $emp['name'],
                'email' => $emp['email'],
                'password' => Hash::make('password123'),
                'role' => 'employee',
            ]);

            $dept = $departments->firstWhere('name', $emp['department']);

            Employee::create([
                'user_id' => $user->id,
                'department_id' => $dept->id,
                'employee_id' => 'EMP-' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'position' => $emp['position'],
                'phone' => $emp['phone'],
                'hire_date' => $emp['hire_date'],
                'basic_salary' => $emp['basic_salary'],
                'allowances' => $emp['allowances'],
                'deductions' => $emp['deductions'],
                'status' => 'active',
            ]);
        }
    }
}
