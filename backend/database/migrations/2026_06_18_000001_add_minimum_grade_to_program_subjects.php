<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_subjects', function (Blueprint $table) {
            // MSCE grade required per subject (1=Distinction … 6=Credit … 8=Pass)
            // Lower number = higher standard required
            $table->tinyInteger('minimum_grade')->default(6)->after('requirement');
        });
    }

    public function down(): void
    {
        Schema::table('program_subjects', function (Blueprint $table) {
            $table->dropColumn('minimum_grade');
        });
    }
};
