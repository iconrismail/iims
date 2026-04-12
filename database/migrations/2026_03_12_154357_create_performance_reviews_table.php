<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users');
            $table->enum('review_period', ['Q1', 'Q2', 'Q3', 'Q4', 'annual']);
            $table->unsignedSmallInteger('period_year');
            $table->json('scores')->nullable();
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->text('comments')->nullable();
            $table->enum('status', ['draft', 'submitted', 'acknowledged'])->default('draft');
            $table->decimal('salary_increment_pct', 5, 2)->default(0);
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'review_period', 'period_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
