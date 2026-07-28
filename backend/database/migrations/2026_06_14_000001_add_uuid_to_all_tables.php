<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $tables = [
        'users',
        'student_profiles',
        'subjects',
        'subject_combinations',
        'careers',
        'university_programs',
        'academic_results',
        'assessment_questions',
        'question_options',
        'assessment_attempts',
        'assessment_responses',
        'recommendations',
        'chatbot_sessions',
        'chatbot_messages',
        'activity_logs',
    ];

    public function up(): void
    {
        // Add nullable uuid column to each table (skip if already exists)
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->char('uuid', 36)->nullable()->unique()->after('id');
                });
            }
        }

        // Populate existing rows with UUIDs
        foreach ($this->tables as $table) {
            DB::table($table)->orderBy('id')->each(function ($row) use ($table) {
                DB::table($table)->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
            });
        }

        // Make uuid not nullable
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->char('uuid', 36)->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }
};
