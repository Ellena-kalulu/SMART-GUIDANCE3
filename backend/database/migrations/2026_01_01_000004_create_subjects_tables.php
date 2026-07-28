<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                   // e.g. Mathematics, Biology
            $table->string('code')->unique();         // e.g. MATH01
            $table->enum('category', [
                'science', 'languages','humanities', 'arts', 'commerce', 'technical', 'general'
            ]);
            $table->text('description')->nullable();
            $table->boolean('is_compulsory')->default(false);
            $table->timestamps();
        });

        // Subject combinations (preset bundles)
        Schema::create('subject_combinations', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // e.g. "Pure Science"
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Which subjects belong to each combination
        Schema::create('combination_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('combination_id')
                  ->constrained('subject_combinations')->onDelete('cascade');
            $table->foreignId('subject_id')
                  ->constrained('subjects')->onDelete('cascade');
            $table->unique(['combination_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combination_subjects');
        Schema::dropIfExists('subject_combinations');
        Schema::dropIfExists('subjects');
    }
};
