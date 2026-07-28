<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // ── Core / Compulsory ──────────────────────────────────────
            ['name' => 'English',                    'code' => 'ENG01',  'category' => 'languages',  'is_compulsory' => true],
            ['name' => 'Mathematics',                'code' => 'MATH01', 'category' => 'science',    'is_compulsory' => true],
            ['name' => 'Chichewa',                   'code' => 'CHI01',  'category' => 'languages',  'is_compulsory' => false],

            // ── Sciences ───────────────────────────────────────────────
            ['name' => 'Biology',                    'code' => 'BIO01',  'category' => 'science',    'is_compulsory' => false],
            ['name' => 'Chemistry',                  'code' => 'CHEM01', 'category' => 'science',    'is_compulsory' => false],
            ['name' => 'Physics',                    'code' => 'PHY01',  'category' => 'science',    'is_compulsory' => false],
            ['name' => 'General Science',            'code' => 'GSCI01', 'category' => 'science',    'is_compulsory' => false],
            ['name' => 'Agriculture',                'code' => 'AGRI01', 'category' => 'science',    'is_compulsory' => false],
            ['name' => 'Additional Mathematics',     'code' => 'MATH02', 'category' => 'science',    'is_compulsory' => false],

            // ── Technology & Technical ─────────────────────────────────
            ['name' => 'Computer Studies',           'code' => 'COMP01', 'category' => 'technical',  'is_compulsory' => false],
            ['name' => 'Technical Drawing',          'code' => 'TECH01', 'category' => 'technical',  'is_compulsory' => false],
            ['name' => 'Woodwork',                   'code' => 'WOOD01', 'category' => 'technical',  'is_compulsory' => false],
            ['name' => 'Metalwork',                  'code' => 'META01', 'category' => 'technical',  'is_compulsory' => false],

            // ── Humanities ─────────────────────────────────────────────
            ['name' => 'History',                    'code' => 'HIST01', 'category' => 'humanities', 'is_compulsory' => false],
            ['name' => 'Geography',                  'code' => 'GEOG01', 'category' => 'humanities', 'is_compulsory' => false],
            ['name' => 'Bible Knowledge',            'code' => 'BK01',   'category' => 'humanities', 'is_compulsory' => false],
            ['name' => 'Social Studies',             'code' => 'SOC01',  'category' => 'humanities', 'is_compulsory' => false],
            ['name' => 'Life Skills',                'code' => 'LIFE01', 'category' => 'humanities', 'is_compulsory' => false],
            ['name' => 'Creative Arts',              'code' => 'ART01',  'category' => 'humanities', 'is_compulsory' => false],
            ['name' => 'Art and Design',             'code' => 'ART02',  'category' => 'humanities', 'is_compulsory' => false],
            ['name' => 'Music',                      'code' => 'MUS01',  'category' => 'humanities', 'is_compulsory' => false],
            ['name' => 'Home Economics',             'code' => 'HOME01', 'category' => 'humanities', 'is_compulsory' => false],

            // ── Languages ──────────────────────────────────────────────
            ['name' => 'French',                     'code' => 'FRE01',  'category' => 'languages',  'is_compulsory' => false],

            // ── Commerce ───────────────────────────────────────────────
            ['name' => 'Business Studies',           'code' => 'BUS01',  'category' => 'commerce',   'is_compulsory' => false],
            ['name' => 'Accounting',                 'code' => 'ACC01',  'category' => 'commerce',   'is_compulsory' => false],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                ['code' => $subject['code']],
                array_merge($subject, ['description' => null])
            );
        }

        $this->command->info('✅ Subjects seeded/updated: ' . count($subjects));
    }
}
