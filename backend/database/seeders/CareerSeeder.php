<?php

namespace Database\Seeders;

use App\Models\{Career, Subject, UniversityProgram};
use Illuminate\Database\Seeder;

class CareerSeeder extends Seeder
{
    public function run(): void
    {
        $careers = $this->careers();

        foreach ($careers as $data) {
            $career = Career::firstOrCreate(
                ['title' => $data['title']],
                [
                    'category'        => $data['category'],
                    'description'     => $data['description'],
                    'required_skills' => $data['required_skills'],
                ]
            );

            // Attach subjects
            foreach ($data['subjects'] as $code => $importance) {
                $subject = Subject::where('code', $code)->first();
                if ($subject && !$career->subjects()->where('subject_id', $subject->id)->exists()) {
                    $career->subjects()->attach($subject->id, ['importance' => $importance]);
                }
            }

            // Link to university programs by name match
            if (!empty($data['programs'])) {
                foreach ($data['programs'] as $programName) {
                    $program = UniversityProgram::where('name', 'like', "%{$programName}%")->first();
                    if ($program && !$career->universityPrograms()->where('program_id', $program->id)->exists()) {
                        $career->universityPrograms()->attach($program->id);
                    }
                }
            }
        }

        $this->command->info('✅ Careers seeded: ' . count($careers));
    }

    private function careers(): array
    {
        return [

            // ── Technology ────────────────────────────────────────────────
            [
                'title'          => 'Software Engineer',
                'category'       => 'Technology',
                'description'    => 'Designs, develops and maintains software applications and systems.',
                'required_skills' => 'Problem solving, programming, logical thinking, mathematics',
                'subjects'       => [
                    'MATH01' => 'required',
                    'PHY01'  => 'required',
                    'COMP01' => 'required',
                    'ENG01'  => 'required',
                    'CHEM01' => 'optional',
                ],
                'programs'       => ['Software Engineering', 'Computer Science', 'Information and Communication Technology', 'Business Information Technology'],
            ],
            [
                'title'          => 'Cybersecurity Analyst',
                'category'       => 'Technology',
                'description'    => 'Protects computer systems and networks from digital attacks and threats.',
                'required_skills' => 'Analytical thinking, networking, attention to detail',
                'subjects'       => [
                    'MATH01' => 'required',
                    'PHY01'  => 'required',
                    'COMP01' => 'recommended',
                    'ENG01'  => 'required',
                ],
                'programs'       => ['Cybersecurity', 'Computer Systems and Security', 'Computer Science'],
            ],
            [
                'title'          => 'Data Scientist',
                'category'       => 'Technology',
                'description'    => 'Analyses large datasets to extract insights and support decision-making.',
                'required_skills' => 'Statistics, mathematics, programming, critical thinking',
                'subjects'       => [
                    'MATH01' => 'required',
                    'COMP01' => 'recommended',
                    'ENG01'  => 'required',
                    'PHY01'  => 'recommended',
                ],
                'programs'       => ['Data Science', 'Mathematical Sciences', 'Statistics', 'Information Systems'],
            ],
            [
                'title'          => 'Network Engineer',
                'category'       => 'Technology',
                'description'    => 'Designs and manages computer networks for organisations.',
                'required_skills' => 'Networking, problem solving, technical knowledge',
                'subjects'       => [
                    'MATH01' => 'required',
                    'PHY01'  => 'required',
                    'COMP01' => 'recommended',
                    'ENG01'  => 'required',
                ],
                'programs'       => ['Computer Network Engineering', 'Applied Computer Science', 'Information Technology'],
            ],

            // ── Health ────────────────────────────────────────────────────
            [
                'title'          => 'Medical Doctor',
                'category'       => 'Health',
                'description'    => 'Diagnoses and treats illnesses and injuries, providing patient care.',
                'required_skills' => 'Empathy, attention to detail, science knowledge, communication',
                'subjects'       => [
                    'BIO01'  => 'required',
                    'CHEM01' => 'required',
                    'PHY01'  => 'required',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                ],
                'programs'       => ['Bachelor of Medicine', 'Biomedical Sciences'],
            ],
            [
                'title'          => 'Nurse',
                'category'       => 'Health',
                'description'    => 'Provides care for patients in hospitals and health facilities.',
                'required_skills' => 'Empathy, communication, attention to detail, resilience',
                'subjects'       => [
                    'BIO01'  => 'required',
                    'CHEM01' => 'required',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'PHY01'  => 'recommended',
                ],
                'programs'       => ['Adult Health Nursing', 'Child Health Nursing', 'Nursing and Midwifery', 'Community Health Nursing'],
            ],
            [
                'title'          => 'Pharmacist',
                'category'       => 'Health',
                'description'    => 'Prepares and dispenses medication and advises patients on drug use.',
                'required_skills' => 'Chemistry knowledge, attention to detail, communication',
                'subjects'       => [
                    'CHEM01' => 'required',
                    'BIO01'  => 'required',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'PHY01'  => 'recommended',
                ],
                'programs'       => ['Bachelor of Pharmacy'],
            ],
            [
                'title'          => 'Medical Laboratory Scientist',
                'category'       => 'Health',
                'description'    => 'Performs laboratory tests to assist in the diagnosis of diseases.',
                'required_skills' => 'Accuracy, science knowledge, analytical thinking',
                'subjects'       => [
                    'BIO01'  => 'required',
                    'CHEM01' => 'required',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'PHY01'  => 'recommended',
                ],
                'programs'       => ['Medical Laboratory Sciences', 'Biomedical Laboratory Science'],
            ],
            [
                'title'          => 'Dentist',
                'category'       => 'Health',
                'description'    => 'Diagnoses and treats dental problems and oral health conditions.',
                'required_skills' => 'Manual dexterity, attention to detail, patient care, science',
                'subjects'       => [
                    'BIO01'  => 'required',
                    'CHEM01' => 'required',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'PHY01'  => 'recommended',
                ],
                'programs'       => ['Bachelor of Dental Surgery'],
            ],

            // ── Engineering ───────────────────────────────────────────────
            [
                'title'          => 'Civil Engineer',
                'category'       => 'Engineering',
                'description'    => 'Designs and supervises construction of infrastructure like roads, bridges and buildings.',
                'required_skills' => 'Mathematics, physics, design, project management',
                'subjects'       => [
                    'MATH01' => 'required',
                    'PHY01'  => 'required',
                    'CHEM01' => 'required',
                    'ENG01'  => 'required',
                    'TECH01' => 'recommended',
                ],
                'programs'       => ['Civil Engineering', 'Agricultural Engineering', 'Environmental Engineering'],
            ],
            [
                'title'          => 'Electrical Engineer',
                'category'       => 'Engineering',
                'description'    => 'Designs and maintains electrical systems and equipment.',
                'required_skills' => 'Mathematics, physics, problem solving, technical knowledge',
                'subjects'       => [
                    'MATH01' => 'required',
                    'PHY01'  => 'required',
                    'CHEM01' => 'required',
                    'ENG01'  => 'required',
                ],
                'programs'       => ['Electrical and Electronics Engineering', 'Electronics and Telecommunication Engineering', 'Sustainable Energy Engineering'],
            ],
            [
                'title'          => 'Mechanical Engineer',
                'category'       => 'Engineering',
                'description'    => 'Designs and manufactures mechanical systems and machines.',
                'required_skills' => 'Mathematics, physics, design, creativity',
                'subjects'       => [
                    'MATH01' => 'required',
                    'PHY01'  => 'required',
                    'CHEM01' => 'required',
                    'ENG01'  => 'required',
                    'TECH01' => 'recommended',
                ],
                'programs'       => ['Mechanical Engineering', 'Manufacturing Engineering'],
            ],

            // ── Agriculture & Environment ─────────────────────────────────
            [
                'title'          => 'Agronomist',
                'category'       => 'Agriculture',
                'description'    => 'Studies crop production and soil management to improve farming yields.',
                'required_skills' => 'Biology, chemistry, fieldwork, problem solving',
                'subjects'       => [
                    'BIO01'  => 'required',
                    'CHEM01' => 'required',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'AGRI01' => 'recommended',
                    'PHY01'  => 'recommended',
                ],
                'programs'       => ['Agriculture', 'Crop Sciences', 'Animal Science', 'Agricultural Engineering'],
            ],
            [
                'title'          => 'Environmental Scientist',
                'category'       => 'Agriculture',
                'description'    => 'Studies the environment and develops solutions to environmental problems.',
                'required_skills' => 'Biology, chemistry, fieldwork, data analysis',
                'subjects'       => [
                    'BIO01'  => 'required',
                    'CHEM01' => 'required',
                    'GEOG01' => 'recommended',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'PHY01'  => 'recommended',
                ],
                'programs'       => ['Environmental Sciences', 'Environmental Engineering', 'Forestry', 'Natural Resources Management'],
            ],
            [
                'title'          => 'Veterinary Doctor',
                'category'       => 'Agriculture',
                'description'    => 'Diagnoses and treats diseases in animals.',
                'required_skills' => 'Biology, empathy, science, problem solving',
                'subjects'       => [
                    'BIO01'  => 'required',
                    'CHEM01' => 'required',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'PHY01'  => 'required',
                ],
                'programs'       => ['Bachelor of Veterinary Medicine'],
            ],

            // ── Business & Commerce ───────────────────────────────────────
            [
                'title'          => 'Accountant',
                'category'       => 'Business',
                'description'    => 'Manages financial records, prepares reports and ensures tax compliance.',
                'required_skills' => 'Mathematics, attention to detail, communication, analytical thinking',
                'subjects'       => [
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'ACC01'  => 'recommended',
                    'BUS01'  => 'recommended',
                ],
                'programs'       => ['Accountancy', 'Commerce in Banking and Finance', 'Internal Auditing'],
            ],
            [
                'title'          => 'Business Manager',
                'category'       => 'Business',
                'description'    => 'Plans and oversees operations within a business or organisation.',
                'required_skills' => 'Leadership, communication, mathematics, decision-making',
                'subjects'       => [
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'BUS01'  => 'recommended',
                    'ACC01'  => 'recommended',
                ],
                'programs'       => ['Business Administration', 'Commerce in Entrepreneurship', 'Agribusiness Management'],
            ],
            [
                'title'          => 'Economist',
                'category'       => 'Business',
                'description'    => 'Analyses economic data to advise on policy and business decisions.',
                'required_skills' => 'Mathematics, analytical thinking, research, communication',
                'subjects'       => [
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'BUS01'  => 'recommended',
                ],
                'programs'       => ['Commerce in Economics', 'Development Economics', 'Economics', 'Agricultural Economics'],
            ],

            // ── Education ─────────────────────────────────────────────────
            [
                'title'          => 'Secondary School Teacher (Science)',
                'category'       => 'Education',
                'description'    => 'Teaches science subjects like Biology, Chemistry, Physics and Mathematics.',
                'required_skills' => 'Communication, patience, subject knowledge, creativity',
                'subjects'       => [
                    'BIO01'  => 'recommended',
                    'CHEM01' => 'recommended',
                    'PHY01'  => 'recommended',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                ],
                'programs'       => ['Education (Science)', 'Education (Mathematics)', 'Education (Biological Sciences)', 'Sciences Education'],
            ],
            [
                'title'          => 'ICT Teacher',
                'category'       => 'Education',
                'description'    => 'Teaches computer and information technology subjects in secondary schools.',
                'required_skills' => 'Computer skills, communication, patience',
                'subjects'       => [
                    'COMP01' => 'required',
                    'MATH01' => 'required',
                    'ENG01'  => 'required',
                    'PHY01'  => 'recommended',
                ],
                'programs'       => ['Education (ICT)', 'Education (Computer Science)', 'Education (Business and Computer Studies)'],
            ],

            // ── Arts & Social Sciences ────────────────────────────────────
            [
                'title'          => 'Journalist',
                'category'       => 'Arts',
                'description'    => 'Researches, writes and reports news for print, broadcast or digital media.',
                'required_skills' => 'Writing, communication, curiosity, research',
                'subjects'       => [
                    'ENG01'  => 'required',
                    'HIST01' => 'recommended',
                    'SOC01'  => 'recommended',
                    'MATH01' => 'optional',
                ],
                'programs'       => ['Journalism', 'Digital Journalism', 'Communication and Cultural Studies', 'Communication Studies'],
            ],
            [
                'title'          => 'Lawyer',
                'category'       => 'Law',
                'description'    => 'Provides legal advice and represents clients in legal proceedings.',
                'required_skills' => 'Critical thinking, communication, research, ethics',
                'subjects'       => [
                    'ENG01'  => 'required',
                    'MATH01' => 'required',
                    'HIST01' => 'recommended',
                    'SOC01'  => 'recommended',
                ],
                'programs'       => ['Arts in Law Enforcement', 'Arts in Politics and Governance', 'Development Studies'],
            ],
            [
                'title'          => 'Social Worker',
                'category'       => 'Arts',
                'description'    => 'Helps individuals and communities address social, emotional and economic challenges.',
                'required_skills' => 'Empathy, communication, problem solving, resilience',
                'subjects'       => [
                    'ENG01'  => 'required',
                    'SOC01'  => 'recommended',
                    'HIST01' => 'recommended',
                    'MATH01' => 'recommended',
                ],
                'programs'       => ['Social Work', 'Sociology', 'Development Studies', 'Psychology'],
            ],
        ];
    }
}
