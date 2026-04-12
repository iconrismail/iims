<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->integer('month'); // 1-12
            $table->integer('year');
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            // One payroll per month per year
            $table->unique(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
