<?php

namespace Database\Seeders;

use App\Models\{StudentProfile, User};
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ============================================================
        // ADMIN USERS
        // ============================================================

        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@careerguide.edu.mw',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // ============================================================
        // TEACHERS
        // ============================================================

        $teachers = [
            [
                'name' => 'Grace Mwale',
                'email' => 'grace.mwale@gmail.com',
                'password' => 'password',
                'role' => 'teacher',
            ],
            [
                'name' => 'James Banda',
                'email' => 'james.banda@gmail.com',
                'password' => 'password',
                'role' => 'teacher',
            ],
            [
                'name' => 'Patricia Chisale',
                'email' => 'patricia.chisale@gmail.com',
                'password' => 'password',
                'role' => 'teacher',
            ],
            [
                'name' => 'Robert Khonje',
                'email' => 'robert.khonje@gmail.com',
                'password' => 'password',
                'role' => 'teacher',
            ],
            [
                'name' => 'Elizabeth Phiri',
                'email' => 'elizabeth.phiri@gmail.com',
                'password' => 'password',
                'role' => 'teacher',
            ],
        ];

        foreach ($teachers as $teacher) {
            User::create($teacher);
        }

        // ============================================================
        // COUNSELLORS
        // ============================================================

        $counsellors = [
            [
                'name' => 'Dr. Mary Kamanga',
                'email' => 'mary.kamanga@gmail.com',
                'password' => 'password',
                'role' => 'counsellor',
            ],
            [
                'name' => 'Peter Chilima',
                'email' => 'peter.chilima@gmail.com',
                'password' => 'password',
                'role' => 'counsellor',
            ],
        ];

        foreach ($counsellors as $counsellor) {
            User::create($counsellor);
        }

        // ============================================================
        // PARENTS
        // ============================================================

        $parents = [
            [
                'name' => 'John Phiri',
                'email' => 'john.phiri@email.com',
                'password' => 'password',
                'role' => 'parent',
            ],
            [
                'name' => 'Mary Banda',
                'email' => 'mary.banda@email.com',
                'password' => 'password',
                'role' => 'parent',
            ],
            [
                'name' => 'Joseph Mwale',
                'email' => 'joseph.mwale@email.com',
                'password' => 'password',
                'role' => 'parent',
            ],
            [
                'name' => 'Alice Chisale',
                'email' => 'alice.chisale@email.com',
                'password' => 'password',
                'role' => 'parent',
            ],
            [
                'name' => 'Frank Kachingwe',
                'email' => 'frank.kachingwe@email.com',
                'password' => 'password',
                'role' => 'parent',
            ],
        ];

        foreach ($parents as $parent) {
            User::create($parent);
        }

        // ============================================================
        // STUDENTS - Form 1 (2024 intake)
        // ============================================================

        $form1Students = [
            [
                'name' => 'Chisomo Phiri',
                'email' => 'chisomo.phiri@gmail.com',
                'student_number' => 'LSS/2024/0001',
                'form_level' => 'Form 1',
                'stream' => 'Science',
            ],
            [
                'name' => 'Tiwonge Banda',
                'email' => 'tiwonge.banda@gmail.com',
                'student_number' => 'LSS/2024/0002',
                'form_level' => 'Form 1',
                'stream' => 'Science',
            ],
            [
                'name' => 'Madalitso Mwale',
                'email' => 'madalitso.mwale@gmail.com',
                'student_number' => 'LSS/2024/0003',
                'form_level' => 'Form 1',
                'stream' => 'Arts',
            ],
            [
                'name' => 'Thoko Chisale',
                'email' => 'thoko.chisale@gmail.com',
                'student_number' => 'LSS/2024/0004',
                'form_level' => 'Form 1',
                'stream' => 'Commerce',
            ],
            [
                'name' => 'Yamikani Kalulu',
                'email' => 'yamikani.kalulu@gmail.com',
                'student_number' => 'LSS/2024/0005',
                'form_level' => 'Form 1',
                'stream' => 'Science',
            ],
            [
                'name' => 'Asimenye Phiri',
                'email' => 'asimenye.phiri@gmail.com',
                'student_number' => 'LSS/2024/0006',
                'form_level' => 'Form 1',
                'stream' => 'Arts',
            ],
            [
                'name' => 'Chikondi Banda',
                'email' => 'chikondi.banda@gmail.com',
                'student_number' => 'LSS/2024/0007',
                'form_level' => 'Form 1',
                'stream' => 'Science',
            ],
            [
                'name' => 'Loveness Mwale',
                'email' => 'loveness.mwale@gmail.com',
                'student_number' => 'LSS/2024/0008',
                'form_level' => 'Form 1',
                'stream' => 'Commerce',
            ],
        ];

        // ============================================================
        // STUDENTS - Form 2
        // ============================================================

        $form2Students = [
            [
                'name' => 'Peter Kamanga',
                'email' => 'peter.kamanga@gmail.com',
                'student_number' => 'LSS/2023/0001',
                'form_level' => 'Form 2',
                'stream' => 'Science',
            ],
            [
                'name' => 'Grace Phiri',
                'email' => 'grace.phiri@gmail.com',
                'student_number' => 'LSS/2023/0002',
                'form_level' => 'Form 2',
                'stream' => 'Science',
            ],
            [
                'name' => 'Emmanuel Banda',
                'email' => 'emmanuel.banda@gmail.com',
                'student_number' => 'LSS/2023/0003',
                'form_level' => 'Form 2',
                'stream' => 'Arts',
            ],
            [
                'name' => 'Patience Mwale',
                'email' => 'patience.mwale@gmail.com',
                'student_number' => 'LSS/2023/0004',
                'form_level' => 'Form 2',
                'stream' => 'Commerce',
            ],
            [
                'name' => 'Wonder Chisale',
                'email' => 'wonder.chisale@gmail.com',
                'student_number' => 'LSS/2023/0005',
                'form_level' => 'Form 2',
                'stream' => 'Science',
            ],
            [
                'name' => 'Ruth Kalulu',
                'email' => 'ruth.kalulu@gmail.com',
                'student_number' => 'LSS/2023/0006',
                'form_level' => 'Form 2',
                'stream' => 'Arts',
            ],
            [
                'name' => 'Isaac Phiri',
                'email' => 'isaac.phiri@gmail.com',
                'student_number' => 'LSS/2023/0007',
                'form_level' => 'Form 2',
                'stream' => 'Science',
            ],
            [
                'name' => 'Martha Banda',
                'email' => 'martha.banda@gmail.com',
                'student_number' => 'LSS/2023/0008',
                'form_level' => 'Form 2',
                'stream' => 'Commerce',
            ],
        ];

        // ============================================================
        // STUDENTS - Form 3
        // ============================================================

        $form3Students = [
            [
                'name' => 'Andrew Mwale',
                'email' => 'andrew.mwale@gmail.com',
                'student_number' => 'LSS/2022/0001',
                'form_level' => 'Form 3',
                'stream' => 'Science',
            ],
            [
                'name' => 'Catherine Phiri',
                'email' => 'catherine.phiri@gmail.com',
                'student_number' => 'LSS/2022/0002',
                'form_level' => 'Form 3',
                'stream' => 'Science',
            ],
            [
                'name' => 'Daniel Banda',
                'email' => 'daniel.banda@gmail.com',
                'student_number' => 'LSS/2022/0003',
                'form_level' => 'Form 3',
                'stream' => 'Arts',
            ],
            [
                'name' => 'Esther Chisale',
                'email' => 'esther.chisale@gmail.com',
                'student_number' => 'LSS/2022/0004',
                'form_level' => 'Form 3',
                'stream' => 'Science',
            ],
            [
                'name' => 'Frank Mwale',
                'email' => 'frank.mwale@gmail.com',
                'student_number' => 'LSS/2022/0005',
                'form_level' => 'Form 3',
                'stream' => 'Commerce',
            ],
            [
                'name' => 'Gift Kalulu',
                'email' => 'gift.kalulu@gmail.com',
                'student_number' => 'LSS/2022/0006',
                'form_level' => 'Form 3',
                'stream' => 'Science',
            ],
            [
                'name' => 'Hellen Phiri',
                'email' => 'hellen.phiri@gmail.com',
                'student_number' => 'LSS/2022/0007',
                'form_level' => 'Form 3',
                'stream' => 'Arts',
            ],
            [
                'name' => 'Ian Banda',
                'email' => 'ian.banda@gmail.com',
                'student_number' => 'LSS/2022/0008',
                'form_level' => 'Form 3',
                'stream' => 'Science',
            ],
        ];

        // ============================================================
        // STUDENTS - Form 4 (Graduating class 2024)
        // ============================================================

        $form4Students = [
            [
                'name' => 'Mary Phiri',
                'email' => 'mary.phiri@gmail.com',
                'student_number' => 'LSS/2021/0001',
                'form_level' => 'Form 4',
                'stream' => 'Science',
                'interests' => 'Mathematics, Physics, Technology',
                'skills' => 'Problem-solving, Analytical thinking',
                'preferred_career' => 'Software Engineering',
            ],
            [
                'name' => 'John Banda',
                'email' => 'john.banda@gmail.com',
                'student_number' => 'LSS/2021/0002',
                'form_level' => 'Form 4',
                'stream' => 'Science',
                'interests' => 'Biology, Chemistry, Medicine',
                'skills' => 'Research, Attention to detail',
                'preferred_career' => 'Medical Doctor',
            ],
            [
                'name' => 'Sarah Mwale',
                'email' => 'sarah.mwale@gmail.com',
                'student_number' => 'LSS/2021/0003',
                'form_level' => 'Form 4',
                'stream' => 'Arts',
                'interests' => 'Literature, History, Law',
                'skills' => 'Communication, Critical thinking',
                'preferred_career' => 'Lawyer',
            ],
            [
                'name' => 'David Chisale',
                'email' => 'david.chisale@gmail.com',
                'student_number' => 'LSS/2021/0004',
                'form_level' => 'Form 4',
                'stream' => 'Commerce',
                'interests' => 'Business, Economics, Finance',
                'skills' => 'Leadership, Negotiation',
                'preferred_career' => 'Business Analyst',
            ],
            [
                'name' => 'Rachel Kalulu',
                'email' => 'rachel.kalulu@gmail.com',
                'student_number' => 'LSS/2021/0005',
                'form_level' => 'Form 4',
                'stream' => 'Science',
                'interests' => 'Engineering, Design, Innovation',
                'skills' => 'Creativity, Technical skills',
                'preferred_career' => 'Civil Engineer',
            ],
            [
                'name' => 'Steven Phiri',
                'email' => 'steven.phiri@gmail.com',
                'student_number' => 'LSS/2021/0006',
                'form_level' => 'Form 4',
                'stream' => 'Arts',
                'interests' => 'Teaching, Psychology, Counseling',
                'skills' => 'Patience, Empathy',
                'preferred_career' => 'Teacher',
            ],
            [
                'name' => 'Linda Banda',
                'email' => 'linda.banda@gmail.com',
                'student_number' => 'LSS/2021/0007',
                'form_level' => 'Form 4',
                'stream' => 'Commerce',
                'interests' => 'Accounting, Finance, Banking',
                'skills' => 'Numerical analysis, Organization',
                'preferred_career' => 'Accountant',
            ],
            [
                'name' => 'Patrick Mwale',
                'email' => 'patrick.mwale@gmail.com',
                'student_number' => 'LSS/2021/0008',
                'form_level' => 'Form 4',
                'stream' => 'Science',
                'interests' => 'Agriculture, Environment, Research',
                'skills' => 'Practical skills, Observation',
                'preferred_career' => 'Agricultural Scientist',
            ],
            [
                'name' => 'Victoria Chisale',
                'email' => 'victoria.chisale@gmail.com',
                'student_number' => 'LSS/2021/0009',
                'form_level' => 'Form 4',
                'stream' => 'Science',
                'interests' => 'Mathematics, Statistics, Data',
                'skills' => 'Analytical, Logical thinking',
                'preferred_career' => 'Data Scientist',
            ],
            [
                'name' => 'Oscar Kalulu',
                'email' => 'oscar.kalulu@gmail.com',
                'student_number' => 'LSS/2021/0010',
                'form_level' => 'Form 4',
                'stream' => 'Arts',
                'interests' => 'Media, Communication, Journalism',
                'skills' => 'Writing, Public speaking',
                'preferred_career' => 'Journalist',
            ],
            [
                'name' => 'Felix Phiri',
                'email' => 'felix.phiri@gmail.com',
                'student_number' => 'LSS/2021/0011',
                'form_level' => 'Form 4',
                'stream' => 'Commerce',
                'interests' => 'Marketing, Sales, Management',
                'skills' => 'Persuasion, Networking',
                'preferred_career' => 'Marketing Manager',
            ],
            [
                'name' => 'Naomi Banda',
                'email' => 'naomi.banda@gmail.com',
                'student_number' => 'LSS/2021/0012',
                'form_level' => 'Form 4',
                'stream' => 'Science',
                'interests' => 'Chemistry, Laboratory work, Research',
                'skills' => 'Precision, Scientific method',
                'preferred_career' => 'Pharmacist',
            ],
        ];

        // Create all students
        $allStudents = array_merge($form1Students, $form2Students, $form3Students, $form4Students);
        $createdStudents = [];

        foreach ($allStudents as $studentData) {
            $user = User::create([
                'name' => $studentData['name'],
                'email' => $studentData['email'],
                'password' => 'password',
                'role' => 'student',
                'is_active' => true,
            ]);

            StudentProfile::create([
                'user_id' => $user->id,
                'student_number' => $studentData['student_number'],
                'form_level' => $studentData['form_level'],
                'stream' => $studentData['stream'],
                'interests' => $studentData['interests'] ?? null,
                'skills' => $studentData['skills'] ?? null,
                'preferred_career' => $studentData['preferred_career'] ?? null,
            ]);

            $createdStudents[] = $user;
        }

        // ============================================================
        // PARENT-STUDENT LINKAGES
        // ============================================================

        // Get parents and students
        $parents = User::where('role', 'parent')->get();
        $students = User::where('role', 'student')->get();

        // Link parents to students (simulating family relationships)
        $parentStudentLinks = [
            // John Phiri's children
            ['parent_id' => $parents[0]->id, 'student_id' => $students[0]->id, 'relationship' => 'Father'],  // Chisomo Phiri
            ['parent_id' => $parents[0]->id, 'student_id' => $students[3]->id, 'relationship' => 'Father'],  // Thoko Chisale

            // Mary Banda's children
            ['parent_id' => $parents[1]->id, 'student_id' => $students[1]->id, 'relationship' => 'Mother'],  // Tiwonge Banda
            ['parent_id' => $parents[1]->id, 'student_id' => $students[4]->id, 'relationship' => 'Mother'],  // Yamikani Kalulu

            // Joseph Mwale's children
            ['parent_id' => $parents[2]->id, 'student_id' => $students[2]->id, 'relationship' => 'Father'],  // Madalitso Mwale
            ['parent_id' => $parents[2]->id, 'student_id' => $students[5]->id, 'relationship' => 'Father'],  // Asimenye Phiri

            // Alice Chisale's children
            ['parent_id' => $parents[3]->id, 'student_id' => $students[8]->id, 'relationship' => 'Mother'],  // Esther Chisale
            ['parent_id' => $parents[3]->id, 'student_id' => $students[11]->id, 'relationship' => 'Mother'], // Ian Banda

            // Frank Kachingwe's children
            ['parent_id' => $parents[4]->id, 'student_id' => $students[12]->id, 'relationship' => 'Father'], // Mary Phiri
            ['parent_id' => $parents[4]->id, 'student_id' => $students[13]->id, 'relationship' => 'Father'], // John Banda
        ];

        foreach ($parentStudentLinks as $link) {
            $parent = User::find($link['parent_id']);
            $student = User::find($link['student_id']);
            if ($parent && $student) {
                $parent->children()->attach($student->id, ['relationship' => $link['relationship']]);
            }
        }

        // Verify all seeded accounts so parent/teacher portals work without email verification step
        User::whereNull('email_verified_at')->update(['email_verified_at' => now()]);

        // ============================================================
        // OUTPUT SUMMARY
        // ============================================================

        $this->command->info('✅ Users seeded successfully!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info("📊 Total Users Created:");
        $this->command->info("   • Admin: 1");
        $this->command->info("   • Teachers: " . count($teachers));
        $this->command->info("   • Counsellors: " . count($counsellors));
        $this->command->info("   • Parents: " . count($parents));
        $this->command->info("   • Students: " . count($allStudents));
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("🔑 Default Password (all accounts): password");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("📧 Test Logins:");
        $this->command->info("   • Admin: admin@careerguide.edu.mw");
        $this->command->info("   • Teacher: grace.mwale@gmail.com");
        $this->command->info("   • Counsellor: mary.kamanga@gmail.com");
        $this->command->info("   • Parent: john.phiri@email.com");
        $this->command->info("   • Student: mary.phiri@gmail.com");
    }
}
