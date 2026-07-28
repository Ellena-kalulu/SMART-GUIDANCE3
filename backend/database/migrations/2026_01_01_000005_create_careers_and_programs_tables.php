<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Career catalogue
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('title');                // e.g. Software Engineer
            $table->string('category');             // e.g. Technology, Health, Law
            $table->text('description')->nullable();
            $table->text('required_skills')->nullable();
            $table->timestamps();
        });

        // Subjects that lead to a career
        Schema::create('career_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('career_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->enum('importance', ['required', 'recommended', 'optional'])
                  ->default('recommended');
            $table->unique(['career_id', 'subject_id']);
        });

        // University programs
        Schema::create('university_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // e.g. BSc Computer Science
            $table->string('university');           // e.g. MUST, Unima, Mzuzu Uni
            $table->string('faculty')->nullable();
            $table->integer('minimum_points')->default(0); // MSCE points cutoff
            $table->text('entry_requirements')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Link university programs to careers
        Schema::create('program_careers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')
                  ->constrained('university_programs')->onDelete('cascade');
            $table->foreignId('career_id')->constrained()->onDelete('cascade');
            $table->unique(['program_id', 'career_id']);
        });

        // Required subjects for each university program
        Schema::create('program_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')
                  ->constrained('university_programs')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->enum('requirement', ['required', 'preferred'])->default('required');
            $table->unique(['program_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_subjects');
        Schema::dropIfExists('program_careers');
        Schema::dropIfExists('university_programs');
        Schema::dropIfExists('career_subjects');
        Schema::dropIfExists('careers');
    }
};
