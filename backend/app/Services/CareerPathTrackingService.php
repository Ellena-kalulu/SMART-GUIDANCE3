<?php

namespace App\Services;

use App\Models\{
    AssessmentAttempt,
    Career,
    StudentGoal,
    UniversityProgram,
    User,
};

class CareerPathTrackingService
{
    public function __construct(
        protected IntelligentAnalysisService $intelligence,
        protected RecommendationEngine $recommendationEngine,
    ) {}

    /**
     * Set or update a student's career path goal.
     */
    public function setGoal(User $student, ?int $careerId, ?int $programId): StudentGoal
    {
        $goal = StudentGoal::updateOrCreate(
            ['student_id' => $student->id],
            [
                'career_id'             => $careerId,
                'university_program_id' => $programId,
                'status'                => 'active',
            ]
        );

        return $this->analyzeGoal($goal->fresh(['career', 'universityProgram']));
    }

    /**
     * Analyze progress toward the student's chosen career/university goal.
     */
    public function analyzeGoal(StudentGoal $goal): StudentGoal
    {
        $goal->loadMissing(['student', 'career', 'universityProgram']);
        $student = $goal->student;
        $program = $goal->universityProgram;
        $career  = $goal->career;

        $snapshot = [];
        $matchScore = null;
        $message = 'Set a career and university programme to start tracking your progress.';

        if ($program) {
            $program->loadMissing(['requiredSubjects', 'careers']);
            $eligibility = $program->calculateEligibility($student);
            $matchScore = $eligibility['match_score'];
            $snapshot = [
                'program'          => $program->name,
                'university'       => $program->university,
                'eligible'         => $eligibility['eligible'],
                'subject_details'  => $eligibility['subjects'],
                'points_met'       => $eligibility['points_met'],
                'required_points'  => $program->minimum_points,
            ];
            $message = $this->buildProgramMessage($eligibility, $goal, $program);
        } elseif ($career) {
            $career->loadMissing('requiredSubjects');
            $careerFit = $this->calculateCareerReadiness($student, $career);
            $matchScore = $careerFit['score'];
            $snapshot = $careerFit;
            $message = $this->buildCareerMessage($careerFit, $goal, $career);
        }

        $previous = $goal->last_match_score;

        $goal->update([
            'previous_match_score' => $previous,
            'last_match_score'     => $matchScore,
            'progress_snapshot'    => $snapshot,
            'tracking_message'     => $message,
            'last_analyzed_at'     => now(),
        ]);

        if ($profile = $student->studentProfile) {
            $profile->update(['preferred_career' => $career?->title ?? $profile->preferred_career]);
        }

        return $goal->fresh(['career', 'universityProgram']);
    }

    /**
     * Re-analyze all active goals (called after grade import).
     */
    public function refreshGoalsForStudents(array $studentIds): void
    {
        StudentGoal::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->with(['student', 'career', 'universityProgram'])
            ->get()
            ->each(fn (StudentGoal $goal) => $this->analyzeGoal($goal));
    }

    /**
     * Refresh intelligence analysis for students after new grades.
     */
    public function refreshAnalysisForStudents(array $studentIds): void
    {
        foreach ($studentIds as $studentId) {
            $student = User::find($studentId);
            if (!$student) {
                continue;
            }

            $attempt = AssessmentAttempt::where('student_id', $studentId)
                ->where('status', 'completed')
                ->latest('completed_at')
                ->first();

            if ($attempt) {
                $this->intelligence->analyzeAndStore($attempt, $student);
                $this->recommendationEngine->generate($attempt->fresh());
            }
        }
    }

    /**
     * Get term-over-term grade trend for subjects relevant to a goal.
     */
    public function getGradeTrend(User $student, ?Career $career = null, ?UniversityProgram $program = null): array
    {
        $subjectIds = collect();

        if ($program) {
            $subjectIds = $program->requiredSubjects->pluck('id');
        } elseif ($career) {
            $subjectIds = $career->requiredSubjects->pluck('id');
        }

        if ($subjectIds->isEmpty()) {
            $subjectIds = $student->academicResults()->pluck('subject_id')->unique();
        }

        return $student->academicResults()
            ->with('subject')
            ->whereIn('subject_id', $subjectIds)
            ->orderBy('term')
            ->get()
            ->groupBy('subject_id')
            ->map(function ($rows) {
                $sorted = $rows->sortBy('term')->values();
                $latest = $sorted->last();
                $earliest = $sorted->first();
                $delta = $sorted->count() >= 2
                    ? round((float) $latest->score - (float) $earliest->score, 1)
                    : 0;

                return [
                    'subject'  => $latest->subject?->name ?? 'Unknown',
                    'latest'   => round((float) $latest->score, 1),
                    'earliest' => round((float) $earliest->score, 1),
                    'delta'    => $delta,
                    'trend'    => $delta > 3 ? 'improving' : ($delta < -3 ? 'declining' : 'stable'),
                    'terms'    => $sorted->map(fn ($r) => [
                        'term'  => $r->term,
                        'score' => round((float) $r->score, 1),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function getTrackingStatus(StudentGoal $goal): array
    {
        $score = (float) ($goal->last_match_score ?? 0);
        $prev  = (float) ($goal->previous_match_score ?? $score);
        $delta = round($score - $prev, 1);

        $status = match (true) {
            $score >= 75 => 'ready',
            $score >= 50 => 'partially_ready',
            default      => 'not_ready',
        };

        if ($delta > 2 && $prev > 0 && $score >= 50) {
            $status = $score >= 75 ? 'ready' : 'partially_ready';
        }

        return [
            'status'  => $status,
            'score'   => $score,
            'delta'   => $delta,
            'label'   => match ($status) {
                'ready'           => 'READY',
                'partially_ready' => 'PARTIALLY READY',
                'not_ready'       => 'NOT YET READY',
                default           => 'Unknown',
            },
            'color'   => match ($status) {
                'ready'           => 'emerald',
                'partially_ready' => 'amber',
                'not_ready'       => 'rose',
                default           => 'gray',
            },
        ];
    }

    /**
     * Check whether the student's grades support their chosen career.
     */
    public function evaluateCareerEligibility(User $student, Career $career): array
    {
        $fit = $this->calculateCareerReadiness($student, $career);
        $score = (float) ($fit['score'] ?? 0);
        $weakSubjects = collect($fit['subjects'] ?? [])
            ->filter(fn ($s) => !($s['met'] ?? false))
            ->pluck('subject')
            ->take(4)
            ->values()
            ->all();

        return [
            'eligible'       => $score >= 75,
            'partially'      => $score >= 50 && $score < 75,
            'score'          => $score,
            'weak_subjects'  => $weakSubjects,
            'subject_details'=> $fit['subjects'] ?? [],
            'message'        => $score >= 75
                ? "You match the requirements for {$career->title} based on your teacher-uploaded grades."
                : ($score >= 50
                    ? "You are partially matched for {$career->title}. Improve " . ($weakSubjects ? implode(', ', $weakSubjects) : 'key subjects') . ' to strengthen your fit.'
                    : "You are not yet matched for {$career->title}. Focus on " . ($weakSubjects ? implode(', ', $weakSubjects) : 'required subjects') . ' and consider alternatives below.'),
        ];
    }

    /**
     * Suggest alternative careers from stored recommendations and grade analysis.
     */
    public function suggestAlternativeCareers(User $student, ?Career $exclude = null, int $limit = 3): array
    {
        return \App\Models\Recommendation::where('student_id', $student->id)
            ->where('type', 'career')
            ->when($exclude, fn ($q) => $q->where('recommended_id', '!=', $exclude->id))
            ->orderByDesc('confidence_score')
            ->with('recommended')
            ->take($limit)
            ->get()
            ->filter(fn ($rec) => $rec->recommended instanceof Career)
            ->map(fn ($rec) => [
                'career'  => $rec->recommended,
                'score'   => round((float) $rec->confidence_score, 1),
                'reason'  => $rec->reason,
            ])
            ->values()
            ->all();
    }

    private function calculateCareerReadiness(User $student, Career $career): array
    {
        $required = $career->requiredSubjects;
        $avgScores = $student->academicResults()
            ->selectRaw('subject_id, AVG(score) as avg_score')
            ->groupBy('subject_id')
            ->pluck('avg_score', 'subject_id');

        $studentSubjectIds = $avgScores->keys();
        $matching = $required->filter(fn ($s) => $studentSubjectIds->contains($s->id));

        $subjectCoverage = $required->isNotEmpty()
            ? ($matching->count() / $required->count()) * 100
            : 50;

        $performance = $matching->map(fn ($s) => (float) ($avgScores->get($s->id, 0)))->avg() ?? 0;

        $attempt = $student->assessmentAttempts()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        $interestBonus = 0;
        if ($attempt) {
            $categories = $attempt->tallyCareerCategories();
            $total = max(1, array_sum($categories));
            $interestBonus = (($categories[$career->category] ?? 0) / $total) * 100;
        }

        $score = round(min(98, ($subjectCoverage * 0.35) + ($performance * 0.45) + ($interestBonus * 0.20)), 1);

        $subjectDetails = $required->map(function ($subject) use ($avgScores) {
            $avg = (float) ($avgScores->get($subject->id, 0));
            return [
                'subject'  => $subject->name,
                'score'    => round($avg, 1),
                'has_data' => $avgScores->has($subject->id),
                'met'      => $avg >= 55,
            ];
        })->values()->all();

        return [
            'score'            => $score,
            'subject_coverage' => round($subjectCoverage, 1),
            'performance'      => round($performance, 1),
            'interest'         => round($interestBonus, 1),
            'subjects'         => $subjectDetails,
        ];
    }

    private function buildProgramMessage(array $eligibility, StudentGoal $goal, UniversityProgram $program): string
    {
        $score = $eligibility['match_score'];
        $prev  = $goal->previous_match_score;

        if ($eligibility['eligible']) {
            $base = "You are currently on track for {$program->name} at {$program->university}.";
        } elseif ($score >= 50) {
            $base = "You are partially ready for {$program->name} at {$program->university}. Keep improving your grades.";
        } else {
            $weak = collect($eligibility['subjects'] ?? [])
                ->filter(fn ($s) => !($s['met'] ?? false))
                ->pluck('subject')
                ->take(3)
                ->implode(', ');
            $base = "You are not yet on track for {$program->name} at {$program->university}. Improve " . ($weak ?: 'key subjects') . ' performance.';
        }

        if ($prev !== null && $score > $prev + 1) {
            $base .= ' Your grades improved since last term — you are getting closer to your goal. Keep working!';
        } elseif ($prev !== null && $score < $prev - 1) {
            $base .= ' Your recent grades dropped slightly. Focus on your weaker subjects to stay on track.';
        }

        return $base;
    }

    private function buildCareerMessage(array $careerFit, StudentGoal $goal, Career $career): string
    {
        $score = $careerFit['score'];
        $prev  = $goal->previous_match_score;

        if ($score >= 75) {
            $base = "You are currently on track for {$career->title}. Your teacher-uploaded grades support this path.";
        } elseif ($score >= 50) {
            $base = "You are partially ready for {$career->title}. Continue improving your grades in required subjects.";
        } else {
            $weak = collect($careerFit['subjects'] ?? [])
                ->filter(fn ($s) => !($s['met'] ?? false))
                ->pluck('subject')
                ->take(3)
                ->implode(', ');
            $base = "You are not yet on track for {$career->title}."
                . ($weak ? " Improve {$weak} performance." : ' Focus on required subjects and consider counselling support.');
        }

        if ($prev !== null && $score > $prev + 1) {
            $base .= ' Great progress — your improved grades show you are moving in the right direction!';
        }

        return $base;
    }
}
