<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('assessment_attempts')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->json('interest_scores')->nullable();
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('predicted_performance')->nullable();
            $table->json('subject_suitability_scores')->nullable();
            $table->json('top_combination')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('confidence_level')->nullable();
            $table->text('explanation')->nullable();
            $table->json('subject_conflicts')->nullable();
            $table->timestamps();

            $table->unique('attempt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_analyses');
    }
};
