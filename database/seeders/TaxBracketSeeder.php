<?php

namespace Database\Seeders;

use App\Models\TaxBracket;
use App\Models\DeductionRule;
use Illuminate\Database\Seeder;

class TaxBracketSeeder extends Seeder
{
    public function run(): void
    {
        // Tax brackets (Sierra Leone-style, NLE)
        $brackets = [
            ['name' => 'Exempt',      'min_salary' => 0,    'max_salary' => 500,  'rate' => 0],
            ['name' => 'Basic Rate',  'min_salary' => 501,  'max_salary' => 2000, 'rate' => 10],
            ['name' => 'Middle Rate', 'min_salary' => 2001, 'max_salary' => 5000, 'rate' => 20],
            ['name' => 'Higher Rate', 'min_salary' => 5001, 'max_salary' => null, 'rate' => 30],
        ];

        foreach ($brackets as $b) {
            TaxBracket::firstOrCreate(['name' => $b['name']], $b);
        }

        // Default deduction rules
        $rules = [
            ['name' => 'NASSIT (Employee)', 'type' => 'percentage', 'value' => 5,   'is_active' => true, 'applies_to' => 'all'],
            ['name' => 'NASSIT (Employer)', 'type' => 'percentage', 'value' => 10,  'is_active' => false,'applies_to' => 'all'],
        ];

        foreach ($rules as $r) {
            DeductionRule::firstOrCreate(['name' => $r['name']], $r);
        }
    }
}
