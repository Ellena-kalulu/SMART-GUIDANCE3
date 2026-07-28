<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('term');          // e.g. "Term 1 2025"
            $table->string('form_level');    // Form 1–4
            $table->decimal('score', 5, 2); // 0.00 – 100.00
            $table->string('grade')->nullable(); // A, B, C, D, E, F
            $table->text('teacher_remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_results');
    }
};
