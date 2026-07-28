<?php

namespace Database\Seeders;

use App\Models\{AssessmentAttempt, AssessmentQuestion, AssessmentResponse, User};
use App\Services\RecommendationEngine;
use Illuminate\Database\Seeder;

class AssessmentSeeder extends Seeder
{
    public function __construct(protected RecommendationEngine $engine) {}

    public function run(): void
    {
        $mcQuestions = AssessmentQuestion::where('is_active', true)
            ->where('type', 'multiple_choice')
            ->with('options')
            ->orderBy('order')
            ->get();

        if ($mcQuestions->isEmpty()) {
            $this->command->warn('⚠ No active multiple-choice questions found — run AssessmentQuestionSeeder first.');
            return;
        }

        $students = User::where('role', 'student')
            ->whereHas('studentProfile', fn($q) => $q->whereIn('form_level', ['Form 3', 'Form 4']))
            ->take(15)
            ->get();

        $seeded = 0;

        foreach ($students as $student) {
            // Skip if already assessed
            if (AssessmentAttempt::where('student_id', $student->id)
                    ->where('status', 'completed')->exists()) {
                continue;
            }

            $attempt = AssessmentAttempt::create([
                'student_id'   => $student->id,
                'completed_at' => now()->subDays(rand(1, 30)),
                'status'       => 'completed',
            ]);

            foreach ($mcQuestions as $question) {
                $option = $question->options->random();
                AssessmentResponse::create([
                    'attempt_id'  => $attempt->id,
                    'question_id' => $question->id,
                    'option_id'   => $option->id,
                ]);
            }

            $this->engine->generate($attempt);
            $seeded++;
        }

        $this->command->info("✅ Sample assessments seeded for {$seeded} students.");
    }
}
