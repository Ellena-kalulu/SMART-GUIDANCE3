<?php

namespace Database\Seeders;

use App\Models\{AcademicResult, StudentProfile, Subject, User};
use Illuminate\Database\Seeder;

/**
 * Seeds Form 1–4 grade history with improving trends for all students.
 */
class HistoricalAcademicProgressSeeder extends Seeder
{
    private const MSCE_SUBJECT_CODES = [
        'ENG01', 'MATH01', 'BIO01', 'CHEM01', 'PHY01',
        'GEOG01', 'HIST01', 'SOC01', 'AGRI01', 'ACC01', 'BUS01',
    ];

    private const FORM_ORDER = ['Form 1', 'Form 2', 'Form 3', 'Form 4'];

    public function run(): void
    {
        $subjects = Subject::whereIn('code', self::MSCE_SUBJECT_CODES)->get()->keyBy('code');

        if ($subjects->isEmpty()) {
            $this->command->warn('⚠ Run SubjectSeeder first.');
            return;
        }

        $students = User::where('role', 'student')->with('studentProfile')->get();
        $seeded = 0;

        foreach ($students as $student) {
            $profile = $student->studentProfile;
            if (!$profile) {
                continue;
            }

            $currentForm = $profile->form_level ?? 'Form 1';
            $currentIdx  = array_search($currentForm, self::FORM_ORDER, true) ?: 0;
            $stream      = $profile->stream ?? 'General';
            $baseBias    = $this->streamBias($stream);

            for ($formIdx = 0; $formIdx <= $currentIdx; $formIdx++) {
                $formLevel = self::FORM_ORDER[$formIdx];

                foreach (['Term 1', 'Term 2', 'Term 3'] as $termIdx => $term) {
                    $termBoost = ($formIdx * 9) + ($termIdx * 3);

                    foreach (self::MSCE_SUBJECT_CODES as $code) {
                        $subject = $subjects->get($code);
                        if (!$subject) {
                            continue;
                        }

                        $base = 52 + $baseBias[$code] + ($student->id % 11);
                        $score = min(98, max(35, round($base + $termBoost + rand(-4, 6), 1)));

                        AcademicResult::updateOrCreate(
                            [
                                'student_id' => $student->id,
                                'subject_id' => $subject->id,
                                'term'       => $term,
                                'form_level' => $formLevel,
                            ],
                            [
                                'score'           => $score,
                                'grade'           => AcademicResult::gradeFromScore($score),
                                'teacher_remarks' => $this->remark($score, $subject->name, $formLevel, $term),
                            ]
                        );
                    }
                }
            }

            $seeded++;
        }

        $this->command->info("✅ Historical academic progress seeded for {$seeded} students.");
    }

    private function streamBias(string $stream): array
    {
        return match ($stream) {
            'Science' => [
                'MATH01' => 18, 'PHY01' => 16, 'CHEM01' => 14, 'BIO01' => 15,
                'ENG01' => 8, 'GEOG01' => 6, 'HIST01' => 2, 'SOC01' => 2,
                'AGRI01' => 4, 'ACC01' => 3, 'BUS01' => 3,
            ],
            'Commerce' => [
                'MATH01' => 12, 'ACC01' => 16, 'BUS01' => 15, 'ENG01' => 10,
                'PHY01' => 4, 'CHEM01' => 3, 'BIO01' => 5, 'GEOG01' => 6,
                'HIST01' => 5, 'SOC01' => 5, 'AGRI01' => 3,
            ],
            'Arts' => [
                'ENG01' => 14, 'HIST01' => 16, 'GEOG01' => 14, 'SOC01' => 12,
                'MATH01' => 4, 'PHY01' => 2, 'CHEM01' => 2, 'BIO01' => 4,
                'AGRI01' => 5, 'ACC01' => 6, 'BUS01' => 6,
            ],
            default => [
                'MATH01' => 8, 'ENG01' => 8, 'BIO01' => 8, 'CHEM01' => 7,
                'PHY01' => 7, 'GEOG01' => 7, 'HIST01' => 6, 'SOC01' => 6,
                'AGRI01' => 10, 'ACC01' => 5, 'BUS01' => 5,
            ],
        };
    }

    private function remark(float $score, string $subject, string $form, string $term): string
    {
        if ($score >= 75) {
            return "Strong performance in {$subject} ({$form}, {$term}).";
        }
        if ($score >= 60) {
            return "Good progress in {$subject} ({$form}, {$term}). Keep working.";
        }

        return "Needs improvement in {$subject} ({$form}, {$term}). Extra support recommended.";
    }
}
