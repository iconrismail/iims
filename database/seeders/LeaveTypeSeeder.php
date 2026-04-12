<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Annual Leave',    'days_per_year' => 21, 'is_paid' => true],
            ['name' => 'Sick Leave',      'days_per_year' => 14, 'is_paid' => true],
            ['name' => 'Maternity Leave', 'days_per_year' => 84, 'is_paid' => true],
            ['name' => 'Unpaid Leave',    'days_per_year' => 30, 'is_paid' => false],
            ['name' => 'Compassionate',   'days_per_year' => 5,  'is_paid' => true],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
