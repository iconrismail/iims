<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->time('time_in')->nullable()->after('status');
            $table->time('time_out')->nullable()->after('time_in');
            $table->enum('shift', ['morning', 'afternoon', 'night'])->nullable()->after('time_out');
            $table->unsignedSmallInteger('late_minutes')->default(0)->after('shift');
        });

        // Add 'late' and 'half_day' as valid status values via a raw ALTER
        // (Laravel enums can't be altered with just the Blueprint helper on MySQL)
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','late','half_day') NOT NULL DEFAULT 'present'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent') NOT NULL DEFAULT 'present'");

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['time_in', 'time_out', 'shift', 'late_minutes']);
        });
    }
};
