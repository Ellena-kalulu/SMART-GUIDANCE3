<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counselling_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counsellor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete(); // null = all students
            $table->dateTime('scheduled_at');
            $table->string('venue');
            $table->text('message')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselling_sessions');
    }
};
