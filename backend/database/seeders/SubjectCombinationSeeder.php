<?php

namespace Database\Seeders;

use App\Models\{Subject, SubjectCombination};
use Illuminate\Database\Seeder;

class SubjectCombinationSeeder extends Seeder
{
    public function run(): void
    {
        $combinations = [
            [
                'name'        => 'Pure Sciences',
                'description' => 'Mathematics, Biology, Chemistry, Physics — ideal for Medicine, Engineering, and Health Sciences.',
                'subjects'    => ['ENG01', 'MATH01', 'BIO01', 'CHEM01', 'PHY01'],
            ],
            [
                'name'        => 'Physical Sciences',
                'description' => 'Mathematics, Physics, Chemistry — ideal for Engineering, Technology, and Applied Sciences.',
                'subjects'    => ['ENG01', 'MATH01', 'PHY01', 'CHEM01'],
            ],
            [
                'name'        => 'Biological Sciences',
                'description' => 'Biology, Chemistry, Agriculture — ideal for Nursing, Pharmacy, Agriculture and Environmental Sciences.',
                'subjects'    => ['ENG01', 'MATH01', 'BIO01', 'CHEM01', 'AGRI01'],
            ],
            [
                'name'        => 'Computer Science',
                'description' => 'Mathematics, Physics, Computer Studies — ideal for ICT, Software Engineering and Cybersecurity.',
                'subjects'    => ['ENG01', 'MATH01', 'PHY01', 'COMP01'],
            ],
            [
                'name'        => 'Commerce',
                'description' => 'Mathematics, Accounting, Business Studies — ideal for Accountancy, Business Administration and Economics.',
                'subjects'    => ['ENG01', 'MATH01', 'ACC01', 'BUS01'],
            ],
            [
                'name'        => 'Arts & Humanities',
                'description' => 'History, Geography, Bible Knowledge, Social Studies — ideal for Law, Journalism, Social Sciences and Education.',
                'subjects'    => ['ENG01', 'HIST01', 'GEOG01', 'SOC01', 'BK01'],
            ],
            [
                'name'        => 'Education Sciences',
                'description' => 'Mathematics, Biology, Chemistry, Physics — ideal for Bachelor of Education (Science) programmes.',
                'subjects'    => ['ENG01', 'MATH01', 'BIO01', 'CHEM01', 'PHY01'],
            ],
            [
                'name'        => 'Agriculture & Environment',
                'description' => 'Biology, Chemistry, Agriculture, Geography — ideal for Agriculture, Forestry, Fisheries and Environmental programmes.',
                'subjects'    => ['ENG01', 'MATH01', 'BIO01', 'CHEM01', 'AGRI01', 'GEOG01'],
            ],
        ];

        foreach ($combinations as $data) {
            $combo = SubjectCombination::firstOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );

            foreach ($data['subjects'] as $code) {
                $subject = Subject::where('code', $code)->first();
                if ($subject && !$combo->subjects()->where('subject_id', $subject->id)->exists()) {
                    $combo->subjects()->attach($subject->id);
                }
            }
        }

        $this->command->info('✅ Subject combinations seeded: ' . count($combinations));
    }
}
