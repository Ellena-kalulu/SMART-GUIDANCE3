<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('disability_type')->nullable()->after('stream');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn('disability_type');
        });
    }
};
