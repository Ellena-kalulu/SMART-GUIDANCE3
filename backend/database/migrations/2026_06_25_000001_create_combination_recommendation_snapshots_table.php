<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('combination_recommendation_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('term')->nullable();
            $table->string('form_level')->nullable();
            $table->string('recommended_combination');
            $table->decimal('score', 5, 2)->default(0);
            $table->json('all_scores')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('combination_recommendation_snapshots');
    }
};
