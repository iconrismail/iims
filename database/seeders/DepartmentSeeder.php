<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resources', 'description' => 'Manages employee relations, recruitment, and workplace policies.'],
            ['name' => 'Engineering', 'description' => 'Software development and technical operations.'],
            ['name' => 'Finance', 'description' => 'Financial planning, accounting, and budgeting.'],
            ['name' => 'Operations', 'description' => 'Day-to-day business operations and logistics.'],
            ['name' => 'Marketing', 'description' => 'Brand management, advertising, and market research.'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }
    }
}
