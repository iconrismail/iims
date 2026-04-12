<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('emergency_contact')->nullable()->after('address');
            $table->string('bank_name')->nullable()->after('emergency_contact');
            $table->string('bank_account')->nullable()->after('bank_name');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['emergency_contact', 'bank_name', 'bank_account']);
        });
    }
};
