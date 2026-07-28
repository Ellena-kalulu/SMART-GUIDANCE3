<?php

namespace Database\Seeders;

use App\Models\{AcademicResult, StudentProfile, Subject, User};
use Illuminate\Database\Seeder;

class AcademicResultSeeder extends Seeder
{
    public function run(): void
    {
        // Get all students
        $students = User::where('role', 'student')->with('studentProfile')->get();

        if ($students->isEmpty()) {
            $this->command->warn('⚠ No students found. Run UserSeeder first.');
            return;
        }

        $terms = ['Term 1', 'Term 2', 'Term 3'];
        $years = ['2023', '2024'];

        $seeded = 0;

        foreach ($students as $student) {
            $profile = $student->studentProfile;
            if (!$profile) continue;

            $formLevel = $profile->form_level;
            $stream = $profile->stream;

            // Determine which subjects this student takes based on form and stream
            $subjects = $this->getSubjectsForStudent($formLevel, $stream);

            if ($subjects->isEmpty()) {
                continue;
            }

            // Determine how many terms to add (older forms have more terms)
            $termsToAdd = $this->getTermsForForm($formLevel, $terms);

            foreach ($termsToAdd as $term) {
                foreach ($subjects as $subject) {
                    // Generate realistic score based on subject difficulty and student stream
                    $score = $this->generateRealisticScore($subject, $stream);
                    $grade = AcademicResult::gradeFromScore($score);

                    AcademicResult::create([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'term' => $term,
                        'form_level' => $formLevel,
                        'score' => $score,
                        'grade' => $grade,
                        'teacher_remarks' => $this->generateTeacherRemark($score, $subject->name),
                    ]);
                }
            }

            $seeded++;
        }

        $this->command->info("✅ Academic results seeded for {$seeded} students.");
        $this->command->info("   Total results created: " . AcademicResult::count());
    }

    /**
     * Get subjects for a student based on form level and stream
     */
    private function getSubjectsForStudent(string $formLevel, ?string $stream)
    {
        // Core subjects for all students
        $coreSubjects = Subject::whereIn('code', ['ENG01', 'MATH01'])->get();

        if ($formLevel === 'Form 1' || $formLevel === 'Form 2') {
            // Junior secondary - general subjects
            $juniorSubjects = Subject::whereIn('code', [
                'CHI01', 'BIO01', 'CHEM01', 'PHY01', 'GEOG01', 'HIST01', 'SOC01', 'LIFE01'
            ])->get();
            return $coreSubjects->merge($juniorSubjects);
        }

        // Senior secondary (Form 3 & 4) - stream-specific
        $streamSubjects = collect();

        switch ($stream) {
            case 'Science':
                $streamSubjects = Subject::whereIn('code', [
                    'BIO01', 'CHEM01', 'PHY01', 'MATH02', 'COMP01'
                ])->get();
                break;
            case 'Arts':
                $streamSubjects = Subject::whereIn('code', [
                    'HIST01', 'GEOG01', 'SOC01', 'BK01', 'FRE01', 'ART01'
                ])->get();
                break;
            case 'Commerce':
                $streamSubjects = Subject::whereIn('code', [
                    'ACC01', 'BUS01', 'ECO01', 'MATH02', 'COMP01'
                ])->get();
                break;
            case 'General':
                $streamSubjects = Subject::whereIn('code', [
                    'BIO01', 'CHEM01', 'GEOG01', 'HIST01', 'BUS01', 'AGRI01'
                ])->get();
                break;
            default:
                $streamSubjects = Subject::whereIn('code', [
                    'BIO01', 'CHEM01', 'GEOG01', 'HIST01', 'BUS01'
                ])->get();
        }

        return $coreSubjects->merge($streamSubjects);
    }

    /**
     * Determine which terms to add based on form level
     */
    private function getTermsForForm(string $formLevel, array $terms): array
    {
        return match ($formLevel) {
            'Form 1' => ['Term 1', 'Term 2', 'Term 3'],
            'Form 2' => ['Term 1', 'Term 2', 'Term 3'],
            'Form 3' => ['Term 1', 'Term 2', 'Term 3'],
            'Form 4' => ['Term 1', 'Term 2', 'Term 3'],
            default => ['Term 2', 'Term 3'],
        };
    }

    /**
     * Generate a realistic score based on subject and student stream
     */
    private function generateRealisticScore(Subject $subject, ?string $stream): float
    {
        // Base score between 40-95
        $baseScore = rand(40, 95);

        // Adjust based on subject category and student stream
        $adjustment = 0;

        // Science stream students do better in science subjects
        if ($stream === 'Science' && in_array($subject->category, ['science', 'technical'])) {
            $adjustment = rand(5, 15);
        }

        // Arts stream students do better in arts subjects
        if ($stream === 'Arts' && $subject->category === 'arts') {
            $adjustment = rand(5, 15);
        }

        // Commerce stream students do better in commerce subjects
        if ($stream === 'Commerce' && $subject->category === 'commerce') {
            $adjustment = rand(5, 15);
        }

        // Adjust for subject difficulty
        $difficultyAdjustment = match ($subject->code) {
            'MATH02', 'PHY01', 'CHEM01' => rand(-10, 0),
            'ACC01' => rand(-5, 5),
            'ENG01', 'CHI01' => rand(0, 10),
            default => 0,
        };

        $score = min(100, max(30, $baseScore + $adjustment + $difficultyAdjustment));

        // Add some randomness within a range
        return round($score + (rand(-5, 5) * 0.5), 1);
    }

    /**
     * Generate teacher remarks based on score
     */
    private function generateTeacherRemark(float $score, string $subjectName): string
    {
        if ($score >= 80) {
            $remarks = [
                "Excellent performance in {$subjectName}! Keep up the great work.",
                "Outstanding understanding of {$subjectName} concepts. Truly exceptional!",
                "Shows remarkable aptitude for {$subjectName}. Continue to excel!",
                "Top-tier performance. A pleasure to teach such a dedicated student.",
            ];
        } elseif ($score >= 70) {
            $remarks = [
                "Very good grasp of {$subjectName}. Keep pushing for excellence.",
                "Strong performance. Shows consistent effort and understanding.",
                "Good progress in {$subjectName}. Can achieve even higher with continued dedication.",
                "Well done! Shows solid understanding of key concepts in {$subjectName}.",
            ];
        } elseif ($score >= 60) {
            $remarks = [
                "Satisfactory performance. Focus on improving weaker areas in {$subjectName}.",
                "Good effort. Review challenging topics to improve your grade.",
                "Shows potential in {$subjectName}. More practice would help.",
                "Adequate understanding. Recommended to seek extra help in difficult areas.",
            ];
        } elseif ($score >= 50) {
            $remarks = [
                "Fair performance. Needs more commitment to {$subjectName} studies.",
                "Passing grade but can improve. Review class notes regularly.",
                "Average performance. Consider joining study groups for {$subjectName}.",
                "Room for improvement. Focus on fundamentals in {$subjectName}.",
            ];
        } else {
            $remarks = [
                "Below average. Requires significant improvement in {$subjectName}.",
                "Needs to put more effort into {$subjectName}. Seek teacher assistance.",
                "Struggling with {$subjectName} concepts. Extra practice recommended.",
                "Performance needs attention. Parent-teacher consultation advised.",
            ];
        }

        return $remarks[array_rand($remarks)];
    }
}

/**
 * Additional Seeder for Specific Student Performance Data
 * This creates more detailed academic records including trends
 */
class DetailedAcademicResultSeeder extends Seeder
{
    public function run(): void
    {
        // Get Form 4 students (graduating class)
        $form4Students = User::where('role', 'student')
            ->whereHas('studentProfile', function ($q) {
                $q->where('form_level', 'Form 4');
            })
            ->with('studentProfile')
            ->get();

        $subjects = Subject::all();

        foreach ($form4Students as $student) {
            $profile = $student->studentProfile;

            // Create a performance trend - improving over time
            for ($term = 1; $term <= 3; $term++) {
                $termName = "Term {$term}";

                // Each term, scores improve slightly
                $improvement = ($term - 1) * 5;

                foreach ($subjects->random(6) as $subject) {
                    // Base score between 50-80, improving each term
                    $baseScore = rand(50, 70);
                    $score = min(95, $baseScore + $improvement + rand(-5, 10));
                    $grade = AcademicResult::gradeFromScore($score);

                    AcademicResult::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'term' => $termName,
                            'form_level' => 'Form 4',
                        ],
                        [
                            'score' => $score,
                            'grade' => $grade,
                            'teacher_remarks' => $this->getDetailedRemark($score, $subject->name, $term),
                        ]
                    );
                }
            }
        }

        $this->command->info("✅ Detailed academic records created for Form 4 students.");
    }

    private function getDetailedRemark(float $score, string $subject, int $term): string
    {
        if ($term === 1) {
            return "Starting {$subject} well. Score: {$score}%. Shows good foundation.";
        } elseif ($term === 2) {
            if ($score >= 70) {
                return "Excellent progress in {$subject}! Score improved to {$score}%. Keep it up!";
            }
            return "Steady improvement in {$subject}. Current score: {$score}%. Focus on weak areas.";
        } else {
            if ($score >= 80) {
                return "Outstanding final performance in {$subject}! Achieved {$score}%. Ready for MSCE.";
            } elseif ($score >= 60) {
                return "Good final standing in {$subject} with {$score}%. Well prepared for exams.";
            }
            return "Final score: {$score}% in {$subject}. Additional revision recommended before MSCE.";
        }
    }
}
