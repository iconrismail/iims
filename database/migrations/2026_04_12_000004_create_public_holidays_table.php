<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');                         // canonical date (year ignored for recurring)
            $table->string('name');
            $table->boolean('is_recurring')->default(true); // repeats every year same M/D
            $table->timestamps();
        });

        // Seed Sierra Leone public holidays (year 2000 used as reference for recurring)
        $holidays = [
            ['date' => '2000-01-01', 'name' => "New Year's Day",         'is_recurring' => true],
            ['date' => '2000-04-27', 'name' => 'Independence Day',        'is_recurring' => true],
            ['date' => '2000-05-01', 'name' => "Workers' Day",            'is_recurring' => true],
            ['date' => '2000-12-25', 'name' => 'Christmas Day',           'is_recurring' => true],
            ['date' => '2000-12-26', 'name' => 'Boxing Day',              'is_recurring' => true],
            // Non-recurring / variable religious holidays — admin can add yearly
            ['date' => '2026-04-03', 'name' => 'Good Friday',             'is_recurring' => false],
            ['date' => '2026-04-06', 'name' => 'Easter Monday',           'is_recurring' => false],
            ['date' => '2026-06-06', 'name' => 'Eid al-Adha',             'is_recurring' => false],
            ['date' => '2026-03-30', 'name' => "Eid al-Fitr",             'is_recurring' => false],
        ];

        foreach ($holidays as $h) {
            DB::table('public_holidays')->insert(array_merge($h, [
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('public_holidays');
    }
};
