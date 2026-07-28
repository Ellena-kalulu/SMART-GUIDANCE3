<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand ENUM to include 'humanities' in case the live column predates this value
        DB::statement("ALTER TABLE subjects MODIFY COLUMN category ENUM('science','languages','humanities','arts','commerce','technical','general') NOT NULL");

        // Reclassify humanities subjects (were 'arts')
        DB::table('subjects')
            ->whereIn('name', ['History', 'Geography', 'Bible Knowledge', 'Life Skills', 'Social Studies',
                               'Creative Arts', 'Art and Design', 'Music', 'Home Economics'])
            ->update(['category' => 'humanities']);

        // French moves from arts to languages
        DB::table('subjects')
            ->where('name', 'French')
            ->update(['category' => 'languages']);

        // Update any existing student_profiles that used old stream names
        DB::table('student_profiles')->where('stream', 'Science')->update(['stream' => 'Sciences']);
        DB::table('student_profiles')->where('stream', 'Arts')->update(['stream' => 'Humanities']);
    }

    public function down(): void
    {
        DB::table('subjects')
            ->whereIn('name', ['History', 'Geography', 'Bible Knowledge', 'Life Skills', 'Social Studies',
                               'Creative Arts', 'Art and Design', 'Music', 'Home Economics'])
            ->update(['category' => 'arts']);

        DB::table('subjects')->where('name', 'French')->update(['category' => 'arts']);
        DB::table('student_profiles')->where('stream', 'Sciences')->update(['stream' => 'Science']);
        DB::table('student_profiles')->where('stream', 'Humanities')->update(['stream' => 'Arts']);
    }
};
