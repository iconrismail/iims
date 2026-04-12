<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'time_in')) {
                $table->time('time_in')->nullable()->after('status');
            }
            if (!Schema::hasColumn('attendances', 'time_out')) {
                $table->time('time_out')->nullable()->after('time_in');
            }
            if (!Schema::hasColumn('attendances', 'shift')) {
                $table->enum('shift', ['morning', 'afternoon', 'night'])->nullable()->after('time_out');
            }
            if (!Schema::hasColumn('attendances', 'late_minutes')) {
                $table->unsignedSmallInteger('late_minutes')->default(0)->after('shift');
            }
        });

        // Add 'late' and 'half_day' as valid status values via a raw ALTER
        // (Laravel enums can't be altered with just the Blueprint helper on MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','late','half_day') NOT NULL DEFAULT 'present'");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent') NOT NULL DEFAULT 'present'");

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['time_in', 'time_out', 'shift', 'late_minutes']);
        });
    }
};
