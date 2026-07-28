<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('career_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('university_program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active'); // active, achieved, paused
            $table->decimal('last_match_score', 5, 2)->nullable();
            $table->decimal('previous_match_score', 5, 2)->nullable();
            $table->json('progress_snapshot')->nullable();
            $table->text('tracking_message')->nullable();
            $table->timestamp('last_analyzed_at')->nullable();
            $table->timestamps();

            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_goals');
    }
};
