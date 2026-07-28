<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SubjectSeeder::class,
            SubjectCombinationSeeder::class,
            UniversityProgramSeeder::class,
            CareerSeeder::class,
            AssessmentQuestionSeeder::class, 
            AssessmentSeeder::class,
            AcademicResultSeeder::class,
            MsceCombinationSeeder::class,
            HistoricalAcademicProgressSeeder::class,
        ]);
    }
}
