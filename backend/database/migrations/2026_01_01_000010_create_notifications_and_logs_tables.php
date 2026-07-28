<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // In-app notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');                          // notification class name
            $table->morphs('notifiable');                   // user receiving it
            $table->text('data');                           // JSON payload
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Audit / activity log for admin tracking
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');          // e.g. login, view_report, submit_assessment
            $table->string('model')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('meta')->nullable();  // extra context
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
    }
};
