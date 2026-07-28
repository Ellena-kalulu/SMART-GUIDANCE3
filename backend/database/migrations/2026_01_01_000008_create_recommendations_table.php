<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('attempt_id')
                  ->nullable()->constrained('assessment_attempts')->onDelete('set null');
            $table->enum('type', ['career', 'subject_combination', 'university_program']);

            // Polymorphic: points to careers, subject_combinations, or university_programs
            $table->unsignedBigInteger('recommended_id');
            $table->string('recommended_type'); // App\Models\Career, etc.

            $table->decimal('confidence_score', 5, 2)->nullable(); // 0–100 match %
            $table->text('reason')->nullable(); // explanation for the recommendation
            $table->enum('status', ['pending', 'viewed', 'accepted', 'dismissed'])
                  ->default('pending');
            $table->timestamps();

            $table->index(['recommended_id', 'recommended_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
