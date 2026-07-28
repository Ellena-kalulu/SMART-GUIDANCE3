<?php

namespace Database\Seeders;

use App\Models\{Subject, SubjectCombination};
use Illuminate\Database\Seeder;

/**
 * Seeds the four MSCE pathway combinations used for grade + interest scoring.
 */
class MsceCombinationSeeder extends Seeder
{
    public function run(): void
    {
        $paths = [
            [
                'name'        => 'Science Combination',
                'description' => 'Mathematics, Physics, Chemistry, Biology — Medicine, Engineering, Health Sciences.',
                'codes'       => ['MATH01', 'PHY01', 'CHEM01', 'BIO01'],
            ],
            [
                'name'        => 'Humanities Combination',
                'description' => 'English, History, Geography, Social Studies — Law, Journalism, Social Sciences.',
                'codes'       => ['ENG01', 'HIST01', 'GEOG01', 'SOC01'],
            ],
            [
                'name'        => 'Commercial Combination',
                'description' => 'Mathematics, Accounting, Business Studies, English — Accountancy, Business, Economics.',
                'codes'       => ['MATH01', 'ACC01', 'BUS01', 'ENG01'],
            ],
            [
                'name'        => 'Agriculture Combination',
                'description' => 'Agriculture, Biology, Geography, Mathematics — Agriculture, Forestry, Environmental Science.',
                'codes'       => ['AGRI01', 'BIO01', 'GEOG01', 'MATH01'],
            ],
        ];

        foreach ($paths as $data) {
            $combo = SubjectCombination::updateOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );

            $subjectIds = Subject::whereIn('code', $data['codes'])->pluck('id');
            $combo->subjects()->sync($subjectIds);
        }

        $this->command->info('✅ MSCE combination paths seeded: ' . count($paths));
    }
}
