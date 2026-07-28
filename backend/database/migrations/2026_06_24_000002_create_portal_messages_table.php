<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['recipient_id', 'read_at']);
            $table->index(['sender_id', 'recipient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_messages');
    }
};
