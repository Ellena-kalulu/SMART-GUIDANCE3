<?php

namespace App\Services;

use App\Models\{
    AssessmentAttempt,
    Career,
    Recommendation,
    SubjectCombination,
    UniversityProgram,
    User,
};

/**
 * RecommendationEngine
 *
 * Rule-based engine that analyses a completed assessment attempt
 * and generates three types of recommendations for a student:
 *   1. Careers
 *   2. Subject Combinations
 *   3. University Programs
 */
class RecommendationEngine
{
    public function __construct(
        protected IntelligentAnalysisService $intelligence,
    ) {}

    /**
     * Generate and persist all recommendations for an attempt.
     * Call this after a student completes an assessment.
     */
    public function generate(AssessmentAttempt $attempt): void
    {
        $student = $attempt->student;
        $categoryScores = $attempt->tallyCareerCategories();

        $analysis = $this->intelligence->analyzeAndStore($attempt, $student);

        // Drop prior attempt recommendations so each assessment produces fresh results
        Recommendation::where('student_id', $student->id)
            ->where('attempt_id', '!=', $attempt->id)
            ->whereNotIn('status', ['accepted'])
            ->delete();

        $this->recommendCareers($attempt, $student, $categoryScores, $analysis);
        $this->recommendSubjectCombinations($attempt, $student, $analysis);
        $this->recommendUniversityPrograms($attempt, $student);
    }

    // ── Step 1: Career recommendations ───────────────────────────

    private function recommendCareers(
        AssessmentAttempt $attempt,
        User $student,
        array $categoryScores,
        \App\Models\AssessmentAnalysis $analysis,
    ): void {
        if (empty($categoryScores)) {
            $categoryScores = $this->interestScoresToCategories($analysis->interest_scores ?? []);
        }

        if (empty($categoryScores)) {
            return;
        }

        $totalVotes = max(1, array_sum($categoryScores));

        $latestGrades = \App\Models\AcademicResult::latestPerSubject($student);
        $studentSubjectIds = $latestGrades->pluck('subject_id');
        $subjectAvgScores = $latestGrades->mapWithKeys(fn ($r) => [$r->subject_id => (float) $r->score]);
        $interestScores = $analysis->interest_scores ?? [];

        // Focus on top interest categories from this student's assessment
        arsort($categoryScores);
        $topCategories = array_slice(array_keys($categoryScores), 0, 3);

        $careers = Career::whereIn('category', $topCategories)
            ->with('subjects')
            ->get();

        $scored = [];

        foreach ($careers as $career) {
            $fit = $this->calculateCareerFitScore($career, $categoryScores, $subjectAvgScores->toArray(), $interestScores);

            if (!$fit['strong_fit']) {
                continue;
            }

            $scored[] = [
                'career' => $career,
                'finalScore' => $fit['final_score'],
                'relevantAvg' => $fit['relevant_avg'],
            ];
        }

        usort($scored, fn ($a, $b) => $b['finalScore'] <=> $a['finalScore']);
        $topCareers = array_slice($scored, 0, 6);

        foreach ($topCareers as ['career' => $career, 'finalScore' => $finalScore, 'relevantAvg' => $relevantAvg]) {
            $strengths = $analysis->strengths ?? [];
            $weaknesses = $analysis->weaknesses ?? [];
            $strengthNote = !empty($strengths)
                ? ' Your strengths in ' . implode(', ', array_slice($strengths, 0, 2)) . ' support this path.'
                : '';
            $gradeNote = $relevantAvg > 0
                ? " Your average grade in related subjects is " . round($relevantAvg) . "%."
                : '';

            $this->saveRecommendation(
                $student,
                $attempt,
                'career',
                $career,
                $finalScore,
                "Based on your assessment, {$career->title} in {$career->category} is a strong match ({$finalScore}% confidence)."
                . $strengthNote
                . $gradeNote
                . (!empty($weaknesses) ? ' Consider improving in ' . implode(', ', array_slice($weaknesses, 0, 2)) . ' to strengthen this path.' : '')
            );
        }
    }

    public function calculateCareerFitScore(
        Career $career,
        array $categoryScores,
        array $subjectAvgScores,
        array $interestScores,
    ): array {
        $totalVotes = max(1, array_sum($categoryScores));
        $categoryVotes = $categoryScores[$career->category] ?? 0;
        $categoryInterest = $totalVotes > 0 ? ($categoryVotes / $totalVotes) * 100 : 0;

        $selfAssessmentInterest = $this->interestScoreForCategory($career->category, $interestScores);
        $interestScore = (float) round(min(100, ($categoryInterest * 0.4) + ($selfAssessmentInterest * 0.6)), 2);

        $requiredSubjectIds = $career->subjects
            ->filter(fn ($s) => $s->pivot->importance === 'required')
            ->pluck('id');

        $matchingIds = $requiredSubjectIds->filter(fn ($id) => (float) ($subjectAvgScores[$id] ?? 0) >= 55);
        $subjectCoverage = $requiredSubjectIds->isNotEmpty()
            ? ($matchingIds->count() / $requiredSubjectIds->count()) * 100
            : 0;

        $relevantScores = $matchingIds->map(fn ($id) => (float) ($subjectAvgScores[$id] ?? 0));
        $relevantAvg = $relevantScores->isNotEmpty() ? $relevantScores->avg() : 0;

        $academicScore = $requiredSubjectIds->isNotEmpty()
            ? (float) round(min(100, ($relevantAvg * 0.75) + ($subjectCoverage * 0.25)), 2)
            : 0;

        $finalScore = (float) round(min(98, ($interestScore * 0.4) + ($academicScore * 0.6)), 2);
        $strongFit = $finalScore >= 60
            && $academicScore >= 60
            && $interestScore >= 50
            && $relevantAvg >= 60;

        return [
            'final_score' => $finalScore,
            'interest_score' => $interestScore,
            'academic_score' => $academicScore,
            'subject_coverage' => round($subjectCoverage, 2),
            'relevant_avg' => (float) round($relevantAvg, 2),
            'strong_fit' => $strongFit,
        ];
    }

    private function interestScoreForCategory(string $category, array $interestScores): float
    {
        $mapping = [
            'Science' => 'Science Interest',
            'Humanities' => 'Humanities Interest',
            'Commercial' => 'Math Interest',
            'Agriculture' => 'Practical Interest',
        ];

        $label = $mapping[$category] ?? null;
        if ($label && isset($interestScores[$label])) {
            return (float) $interestScores[$label];
        }

        return (float) collect($interestScores)->max() ?: 0;
    }

    // ── Step 2: Subject combination recommendations ───────────────

    private function recommendSubjectCombinations(
        AssessmentAttempt $attempt,
        User $student,
        \App\Models\AssessmentAnalysis $analysis,
    ): void {
        $suitability = $analysis->subject_suitability_scores ?? ['details' => []];
        $interestScores = $analysis->interest_scores ?? [];
        $strengths = $analysis->strengths ?? [];
        $weaknesses = $analysis->weaknesses ?? [];

        $msceNames = ['Science Combination', 'Commercial Combination', 'Humanities Combination', 'Agriculture Combination'];
        $combinations = SubjectCombination::with('subjects')
            ->whereIn('name', $msceNames)
            ->get();

        if ($combinations->isEmpty()) {
            $combinations = SubjectCombination::with('subjects')->get();
        }

        $scored = [];

        foreach ($combinations as $combo) {
            $detail = collect($suitability['details'] ?? [])->firstWhere('combination', $combo->name);
            $baseScore = (float) ($detail['score'] ?? 0);
            $finalScore = min(98, round($baseScore, 2));

            if ($finalScore <= 0) {
                continue;
            }

            $reason = $this->intelligence->combinationReason(
                $combo,
                $suitability,
                $interestScores,
                $strengths,
                $weaknesses
            );

            $scored[] = ['combo' => $combo, 'finalScore' => $finalScore, 'reason' => $reason];
        }

        usort($scored, fn ($a, $b) => $b['finalScore'] <=> $a['finalScore']);

        foreach (array_slice($scored, 0, 4) as $item) {
            $this->saveRecommendation(
                $student,
                $attempt,
                'subject_combination',
                $item['combo'],
                $item['finalScore'],
                $item['reason']
            );
        }
    }

    // ── Step 3: University program recommendations ────────────────

    private function recommendUniversityPrograms(
        AssessmentAttempt $attempt,
        User $student
    ): void {
        // Get career IDs already recommended for this attempt
        $recommendedCareerIds = Recommendation::where('attempt_id', $attempt->id)
            ->where('type', 'career')
            ->pluck('recommended_id');

        if ($recommendedCareerIds->isEmpty()) {
            return;
        }

        $programs = UniversityProgram::whereHas('careers', fn ($q) =>
            $q->whereIn('careers.id', $recommendedCareerIds)
        )->with('requiredSubjects')->get();

        $scored = [];
        foreach ($programs as $program) {
            $eligibility = $program->calculateEligibility($student);
            $scored[] = ['program' => $program, 'eligibility' => $eligibility, 'score' => (float) $eligibility['match_score']];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        foreach (array_slice($scored, 0, 5) as $item) {
            $program = $item['program'];
            $eligibility = $item['eligibility'];
            $matchScore = $item['score'];

            $this->saveRecommendation(
                $student,
                $attempt,
                'university_program',
                $program,
                $matchScore,
                $eligibility['eligible']
                    ? "{$program->name} at {$program->university} matches your recommended careers. "
                      . "You meet {$eligibility['coverage']}% of subject requirements ({$matchScore}% readiness)."
                    : "{$program->name} at {$program->university} is relevant to your career interests. "
                      . "Current readiness: {$matchScore}%. Focus on required subjects to improve eligibility."
            );
        }
    }

    // ── Shared save helper ────────────────────────────────────────

    private function saveRecommendation(
        User   $student,
        AssessmentAttempt $attempt,
        string $type,
        $model,
        float  $score,
        string $reason
    ): void {
        Recommendation::updateOrCreate(
            [
                'student_id'       => $student->id,
                'attempt_id'       => $attempt->id,
                'type'             => $type,
                'recommended_id'   => $model->id,
                'recommended_type' => get_class($model),
            ],
            [
                'confidence_score' => $score,
                'reason'           => $reason,
                'status'           => 'pending',
            ]
        );
    }
}
