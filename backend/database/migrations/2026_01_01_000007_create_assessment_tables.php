<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assessment questions bank
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->enum('type', ['multiple_choice', 'scale', 'text'])->default('multiple_choice');
            $table->string('category')->nullable(); // interests, skills, personality
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Answer options for multiple_choice questions
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')
                  ->constrained('assessment_questions')->onDelete('cascade');
            $table->string('option_text');
            $table->string('mapped_career_category')->nullable(); // links answer to career type
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Each time a student takes the assessment
        Schema::create('assessment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->timestamps();
        });

        // Individual answers per attempt
        Schema::create('assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')
                  ->constrained('assessment_attempts')->onDelete('cascade');
            $table->foreignId('question_id')
                  ->constrained('assessment_questions')->onDelete('cascade');
            // For multiple_choice, store the chosen option id
            $table->foreignId('option_id')
                  ->nullable()->constrained('question_options')->onDelete('set null');
            // For scale or text answers
            $table->text('text_response')->nullable();
            $table->integer('scale_value')->nullable(); // 1–5
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_responses');
        Schema::dropIfExists('assessment_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('assessment_questions');
    }
};
