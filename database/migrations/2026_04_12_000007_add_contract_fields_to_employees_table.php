<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('contract_type', ['permanent', 'fixed-term', 'probation'])
                  ->default('permanent')
                  ->after('status');
            $table->date('contract_end_date')->nullable()->after('contract_type');
            $table->date('probation_end_date')->nullable()->after('contract_end_date');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['contract_type', 'contract_end_date', 'probation_end_date']);
        });
    }
};
