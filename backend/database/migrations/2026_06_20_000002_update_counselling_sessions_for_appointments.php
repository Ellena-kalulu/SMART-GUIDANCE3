<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        // Add 'pending_confirmation' to status enum
        DB::statement("ALTER TABLE counselling_sessions MODIFY COLUMN status ENUM('pending_confirmation','scheduled','completed','cancelled') DEFAULT 'scheduled'");

        // Make venue nullable (for online/phone appointments)
        DB::statement("ALTER TABLE counselling_sessions MODIFY COLUMN venue VARCHAR(255) NULL");

        Schema::table('counselling_sessions', function (Blueprint $table) {
            $table->enum('initiated_by', ['counsellor', 'student'])->default('counsellor')->after('status');
            $table->enum('appointment_type', ['in_person', 'online', 'phone'])->default('in_person')->after('initiated_by');
            $table->text('student_reason')->nullable()->after('appointment_type');
            $table->text('counsellor_notes')->nullable()->after('student_reason');
        });
    }

    public function down(): void
    {
        Schema::table('counselling_sessions', function (Blueprint $table) {
            $table->dropColumn(['initiated_by', 'appointment_type', 'student_reason', 'counsellor_notes']);
        });

        DB::statement("ALTER TABLE counselling_sessions MODIFY COLUMN venue VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE counselling_sessions MODIFY COLUMN status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled'");
    }
};
