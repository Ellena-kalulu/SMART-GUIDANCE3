<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->string('section')->nullable()->after('category');
            $table->json('scale_labels')->nullable()->after('section');
        });

        // Add 'checkbox' to the type enum
        DB::statement("ALTER TABLE assessment_questions MODIFY COLUMN type ENUM('multiple_choice','scale','text','checkbox') DEFAULT 'multiple_choice'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE assessment_questions MODIFY COLUMN type ENUM('multiple_choice','scale','text') DEFAULT 'multiple_choice'");

        Schema::table('assessment_questions', function (Blueprint $table) {
            $table->dropColumn(['section', 'scale_labels']);
        });
    }
};
