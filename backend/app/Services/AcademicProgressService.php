<?php

namespace App\Services;

use App\Models\{
    AssessmentAnalysis,
    CombinationRecommendationSnapshot,
    StudentGoal,
    User,
};
use Illuminate\Support\Collection;

class AcademicProgressService
{
    public const GRADE_WEIGHT    = 0.70;
    public const INTEREST_WEIGHT = 0.30;
    public const READY_MIN       = 70;
    public const PARTIAL_MIN     = 55;

    public function __construct(
        protected IntelligentAnalysisService $intelligence,
    ) {}

    public function getFullReport(User $student, ?AssessmentAnalysis $analysis = null): array
    {
        $results = $student->academicResults()->with('subject')->get();
        $paths   = $this->intelligence->analyzeMscePaths($student, $analysis);
        $topPath = $paths[0] ?? null;

        return [
            'overview'              => $this->getPerformanceOverview($student, $results, $topPath),
            'subject_progress'      => $this->getSubjectProgressTable($results),
            'strengths_weaknesses'  => $this->getStrengthsWeaknesses($results),
            'trend_analysis'        => $this->getTrendAnalysis($results),
            'goal_tracking'         => $this->getGoalTracking($student, $paths),
            'early_warnings'        => $this->getEarlyWarnings($student, $results, $topPath),
            'improvement_advice'    => $this->getImprovementAdvice($student, $results, $paths),
            'combination_readiness' => $this->getCombinationReadiness($paths),
            'weak_subject_impact'   => $this->getWeakSubjectImpact($paths),
            'combination_comparison'=> $this->getCombinationComparison($paths),
            'recommendation_stability' => $this->getRecommendationStability($student, $analysis),
            'peer_comparison'       => $this->getPeerComparison($student, $topPath),
            'improvement_roadmap'   => $this->getImprovementRoadmap($student, $paths, $results),
            'academic_health'       => $this->getAcademicHealth($results),
            'predictions'           => $this->getPredictions($results),
            'milestones'            => $this->getMilestones($results),
            'msce_paths'            => $paths,
        ];
    }

    public function getPerformanceOverview(User $student, ?Collection $results = null, ?array $topPath = null): array
    {
        $results = $results ?? $student->academicResults()->with('subject')->get();
        $avg     = $results->avg('score');
        $trend   = $this->overallTrend($results);

        return [
            'performance_level' => match (true) {
                $avg === null       => 'No Data',
                $avg >= 75          => 'Excellent',
                $avg >= 65          => 'Good',
                $avg >= 50          => 'Average',
                default             => 'Needs Support',
            },
            'overall_average'   => $avg !== null ? round($avg, 1) : null,
            'trend'             => $trend['label'],
            'trend_direction'   => $trend['direction'],
            'recommended_path'  => $topPath['path'] ?? 'Complete assessment',
            'recommended_score' => $topPath['score'] ?? 0,
        ];
    }

    public function getSubjectProgressTable(Collection $results): array
    {
        $forms = ['Form 1', 'Form 2', 'Form 3', 'Form 4'];
        $rows  = [];

        $bySubject = $results->groupBy(fn ($r) => $r->subject?->name ?? 'Unknown');

        foreach ($bySubject as $subject => $subjectResults) {
            $formScores = [];
            foreach ($forms as $form) {
                $formScores[$form] = round((float) $subjectResults->where('form_level', $form)->avg('score'), 0) ?: null;
            }

            $values = array_filter($formScores, fn ($v) => $v !== null);
            $trend  = 'stable';
            if (count($values) >= 2) {
                $first = reset($values);
                $last  = end($values);
                $trend = $last > $first + 3 ? 'improving' : ($last < $first - 3 ? 'declining' : 'stable');
            }

            $rows[] = [
                'subject' => $subject,
                'forms'   => $formScores,
                'trend'   => $trend,
            ];
        }

        usort($rows, fn ($a, $b) => ($b['forms']['Form 4'] ?? $b['forms']['Form 3'] ?? 0) <=> ($a['forms']['Form 4'] ?? $a['forms']['Form 3'] ?? 0));

        return $rows;
    }

    public function getStrengthsWeaknesses(?Collection $results = null): array
    {
        if (!$results || $results->isEmpty()) {
            return ['strengths' => [], 'weaknesses' => []];
        }

        $averages = $this->latestPerSubjectFrom($results)->map(fn ($r) => [
            'name'  => $r->subject?->name ?? 'Unknown',
            'score' => round((float) $r->score, 1),
        ])->sortByDesc('score')->values();

        $avg = $averages->avg('score');

        return [
            'strengths'  => $averages->where('score', '>=', 70)->pluck('name')->take(5)->values()->all(),
            'weaknesses' => $averages->where('score', '<', 55)->pluck('name')->take(5)->values()->all(),
        ];
    }

    public function getTrendAnalysis(Collection $results): array
    {
        $analysis = [];

        foreach ($results->groupBy('subject_id') as $rows) {
            $name = $rows->first()->subject?->name ?? 'Unknown';
            $byForm = $rows->sortBy(fn ($r) => array_search($r->form_level, ['Form 1', 'Form 2', 'Form 3', 'Form 4'], true))
                ->groupBy('form_level')
                ->map(fn ($f) => round((float) $f->avg('score'), 1));

            if ($byForm->count() < 2) {
                continue;
            }

            $first = $byForm->first();
            $last  = $byForm->last();
            $delta = round($last - $first, 1);

            $analysis[] = [
                'subject'    => $name,
                'trend'      => $delta > 3 ? 'Improving' : ($delta < -3 ? 'Declining' : 'Stable'),
                'change'     => $delta,
                'status'     => match (true) {
                    $delta >= 15 => 'Excellent Progress',
                    $delta >= 8  => 'Good Progress',
                    $delta >= 0  => 'Steady',
                    $delta >= -8 => 'Needs Attention',
                    default      => 'At Risk',
                },
            ];
        }

        return $analysis;
    }

    public function getGoalTracking(User $student, array $paths): array
    {
        $goal = StudentGoal::where('student_id', $student->id)->with('career')->first();
        $targetPath = $paths[0]['path'] ?? 'Science Path';

        if ($goal?->career) {
            $cat = strtolower($goal->career->category ?? '');
            $targetPath = match (true) {
                str_contains($cat, 'science') || str_contains($cat, 'health') || str_contains($cat, 'engineering') => 'Science Path',
                str_contains($cat, 'business') || str_contains($cat, 'commerce') => 'Commercial Path',
                str_contains($cat, 'agric') => 'Agriculture Path',
                default => 'Humanities Path',
            };
        }

        $pathData = collect($paths)->firstWhere('path', $targetPath) ?? ($paths[0] ?? null);
        if (!$pathData) {
            return ['target' => $targetPath, 'met' => [], 'needs_improvement' => []];
        }

        $met = [];
        $needs = [];
        foreach ($pathData['grade_scores'] as $sub => $score) {
            if ($score >= self::READY_MIN) {
                $met[] = $sub;
            } else {
                $needs[] = $sub;
            }
        }

        return [
            'target'             => $targetPath,
            'met'                => $met,
            'needs_improvement'  => $needs,
            'readiness'          => $pathData['readiness'] ?? $this->readinessLabel($pathData['score'] ?? 0),
        ];
    }

    public function getEarlyWarnings(User $student, Collection $results, ?array $topPath): array
    {
        $warnings = [];

        foreach ($results->groupBy('subject_id') as $rows) {
            $byTerm = $rows->sortBy('term')->groupBy(fn ($r) => ($r->form_level ?? '') . '|' . ($r->term ?? ''));
            $keys   = $byTerm->keys()->values();

            if ($keys->count() < 2) {
                continue;
            }

            $prevAvg = $byTerm->get($keys[$keys->count() - 2])->avg('score');
            $lastAvg = $byTerm->get($keys->last())->avg('score');
            $name    = $rows->first()->subject?->name ?? 'Subject';

            if ($lastAvg < $prevAvg - 5) {
                $warnings[] = [
                    'type'    => 'decline',
                    'subject' => $name,
                    'message' => "{$name} performance has declined for two consecutive terms. This may affect your " . ($topPath['path'] ?? 'recommended') . " recommendation.",
                ];
            }
        }

        return $warnings;
    }

    public function getImprovementAdvice(User $student, Collection $results, array $paths): array
    {
        $advice = [];
        $sw     = $this->getStrengthsWeaknesses($results);
        $top    = $paths[0] ?? null;

        foreach ($sw['weaknesses'] as $weak) {
            $advice[] = "Focus on {$weak} revision.";
        }

        if ($top && !empty($top['improvements'])) {
            foreach (array_slice($top['improvements'], 0, 2) as $imp) {
                $advice[] = "Improve {$imp} to strengthen your {$top['path']} recommendation.";
            }
        }

        foreach ($sw['strengths'] as $strong) {
            $advice[] = "Maintain {$strong} above 75%.";
        }

        return array_slice(array_unique($advice), 0, 6);
    }

    public function getCombinationReadiness(array $paths): array
    {
        return collect($paths)->map(function ($path) {
            $avg = !empty($path['grade_scores'])
                ? array_sum($path['grade_scores']) / count($path['grade_scores'])
                : 0;

            return [
                'combination' => str_replace(' Path', '', $path['path']),
                'status'      => $this->readinessFromGrades($path['grade_scores'] ?? []),
                'score'       => $path['score'],
                'grade_avg'   => round($avg, 1),
            ];
        })->values()->all();
    }

    public function getWeakSubjectImpact(array $paths): array
    {
        $top = $paths[0] ?? null;
        if (!$top) {
            return [];
        }

        $holding = collect($top['grade_scores'] ?? [])
            ->filter(fn ($score) => $score < self::PARTIAL_MIN)
            ->map(fn ($score, $sub) => ['subject' => $sub, 'score' => $score])
            ->values()
            ->all();

        return [
            'combination' => str_replace(' Path', '', $top['path']),
            'status'      => empty($holding) ? 'Eligible' : 'Almost Eligible',
            'holding_back' => $holding,
        ];
    }

    public function getCombinationComparison(array $paths): array
    {
        $top = $paths[0]['path'] ?? null;

        return collect($paths)->map(function ($path, $idx) use ($top) {
            $label = str_replace(' Path', '', $path['path']);
            $status = match (true) {
                $path['path'] === $top => 'Recommended',
                ($path['score'] ?? 0) >= self::READY_MIN => 'Alternative',
                ($path['score'] ?? 0) >= self::PARTIAL_MIN => 'Possible',
                default => 'Not Suitable',
            };

            return [
                'combination' => $label,
                'score'       => $path['score'],
                'status'      => $status,
                'rank'        => $idx + 1,
            ];
        })->values()->all();
    }

    public function simulateGradeImprovement(User $student, ?AssessmentAnalysis $analysis, string $subject, float $newScore): array
    {
        $results = $student->academicResults()->with('subject')->get();
        $modified = $results->map(function ($r) use ($subject, $newScore) {
            if (strcasecmp($r->subject?->name ?? '', $subject) === 0
                || str_contains(strtolower($r->subject?->name ?? ''), strtolower($subject))) {
                $clone = clone $r;
                $clone->score = $newScore;
                return $clone;
            }
            return $r;
        });

        $originalPaths = $this->intelligence->analyzeMscePaths($student, $analysis);
        $simulatedPaths = $this->simulatePathsWithResults($modified, $analysis);

        return [
            'current_recommendation' => str_replace(' Path', '', $originalPaths[0]['path'] ?? 'Unknown'),
            'new_recommendation'     => str_replace(' Path', '', $simulatedPaths[0]['path'] ?? 'Unknown'),
            'subject'                => $subject,
            'new_score'              => $newScore,
            'paths'                  => $simulatedPaths,
        ];
    }

    public function getRecommendationStability(User $student, ?AssessmentAnalysis $analysis): array
    {
        $snapshots = CombinationRecommendationSnapshot::where('student_id', $student->id)
            ->orderBy('created_at')
            ->get();

        if ($snapshots->count() >= 2) {
            return $snapshots->map(fn ($s) => [
                'period'       => ($s->form_level ?? '') . ' ' . ($s->term ?? $s->created_at?->format('M Y')),
                'combination'  => $s->recommended_combination,
                'score'        => $s->score,
            ])->values()->all();
        }

        $history = $this->intelligence->getRecommendationHistory($student);

        return collect($history)->map(fn ($h) => [
            'period'      => $h['date'] ?? $h['assessment'],
            'combination' => str_replace(' Combination', '', $h['combination'] ?? '—'),
            'score'       => $h['score'] ?? 0,
        ])->values()->all();
    }

    public function getPeerComparison(User $student, ?array $topPath): array
    {
        if (!$topPath || empty($topPath['subjects'])) {
            return [];
        }

        $peerScores = \App\Models\AcademicResult::query()
            ->where('student_id', '!=', $student->id)
            ->whereHas('student', fn ($q) => $q->where('role', 'student'))
            ->with('subject')
            ->get()
            ->groupBy(fn ($r) => strtolower($r->subject?->name ?? ''))
            ->map(fn ($rows) => round((float) $rows->avg('score'), 0));

        $comparisons = [];
        foreach ($topPath['subjects'] as $sub) {
            $studentScore = $topPath['grade_scores'][$sub] ?? null;
            $peerAvg      = $this->findPeerScore($peerScores, $sub);

            if ($studentScore === null) {
                continue;
            }

            $comparisons[] = [
                'subject'       => $sub,
                'peer_typical'  => ($peerAvg ?? 70) . '%+',
                'your_score'    => $studentScore,
                'above_peer'    => $peerAvg === null || $studentScore >= $peerAvg,
            ];
        }

        return $comparisons;
    }

    public function getImprovementRoadmap(User $student, array $paths, Collection $results): array
    {
        $goal = $paths[0] ?? null;
        if (!$goal) {
            return [];
        }

        $steps = [];
        foreach ($goal['improvements'] ?? [] as $i => $sub) {
            $steps[] = ($i + 1) . '. Improve ' . $sub . ' by at least 10%.';
        }

        foreach ($this->getStrengthsWeaknesses($results)['strengths'] as $strong) {
            $steps[] = count($steps) + 1 . '. Maintain ' . $strong . ' above 75%.';
        }

        $steps[] = count($steps) + 1 . '. Attend revision sessions for weak science subjects.';
        $steps[] = count($steps) + 1 . '. Complete aptitude activities for your target path.';

        return [
            'goal'      => str_replace(' Path', '', $goal['path']),
            'steps'     => array_slice($steps, 0, 5),
            'readiness' => 'Next school term if improvements continue.',
        ];
    }

    public function getAcademicHealth(Collection $results): array
    {
        $avg = $results->avg('score');
        $trend = $this->overallTrend($results);

        $level = match (true) {
            $avg === null => 'Unknown',
            $avg >= 75 && $trend['direction'] !== 'down' => 'Excellent',
            $avg >= 65 => 'Good',
            $avg >= 50 => 'Average',
            default => 'At Risk',
        };

        return ['level' => $level, 'average' => $avg ? round($avg, 1) : null];
    }

    public function getPredictions(Collection $results): array
    {
        $predictions = [];

        foreach ($results->groupBy('subject_id') as $rows) {
            $sorted = $rows->sortBy(fn ($r) => ($r->form_level ?? '') . ($r->term ?? ''));
            $scores = $sorted->pluck('score')->map(fn ($s) => (float) $s)->values();

            if ($scores->count() < 2) {
                continue;
            }

            $avg = $scores->avg();
            $delta = $scores->last() - $scores->first();
            $projected = min(100, max(0, $avg + ($delta * 0.35)));

            $predictions[] = [
                'subject' => $rows->first()->subject?->name ?? 'Unknown',
                'range'   => round(max(0, $projected - 3), 0) . '% – ' . round(min(100, $projected + 3), 0) . '%',
            ];
        }

        return array_slice($predictions, 0, 8);
    }

    public function getMilestones(Collection $results): array
    {
        $milestones = [];

        foreach ($results->groupBy('subject_id') as $rows) {
            $byForm = $rows->groupBy('form_level')->map(fn ($f) => (float) $f->avg('score'));
            if ($byForm->count() < 2) {
                continue;
            }

            $first = $byForm->first();
            $last  = $byForm->last();
            $delta = round($last - $first, 0);

            if ($delta >= 15) {
                $milestones[] = ($rows->first()->subject?->name ?? 'Subject') . " improved by {$delta}% over your academic journey.";
            }
        }

        return array_slice($milestones, 0, 3);
    }

    public function getTeacherInterventions(User $student, ?AssessmentAnalysis $analysis = null): array
    {
        // Only compute the specific pieces this needs — not the full 17-part
        // report (getFullReport) — since this runs once per student in a loop
        // on the teacher dashboard and the extra sub-reports are unused here.
        $results  = $student->relationLoaded('academicResults')
            ? $student->academicResults
            : $student->academicResults()->with('subject')->get();
        $paths    = $this->intelligence->analyzeMscePaths($student, $analysis);
        $topPath  = $paths[0] ?? null;
        $warnings = $this->getEarlyWarnings($student, $results, $topPath);
        $weak     = $this->getWeakSubjectImpact($paths)['holding_back'][0]['subject'] ?? null;

        if (empty($warnings) && !$weak) {
            return [];
        }

        $actions = [];
        if ($weak) {
            $actions[] = "Provide {$weak} support sessions.";
        }
        foreach ($warnings as $w) {
            $actions[] = 'Monitor ' . $w['subject'] . ' performance closely.';
        }

        return [[
            'student'    => $student->name,
            'recommendation' => str_replace(' Path', '', $topPath['path'] ?? 'Pending'),
            'risk'       => $weak ? "Low {$weak} performance." : ($warnings[0]['message'] ?? 'Needs attention'),
            'action'     => implode(' ', $actions),
        ]];
    }

    public function snapshotRecommendation(User $student, array $paths, ?string $term = null, ?string $form = null): void
    {
        $top = $paths[0] ?? null;
        if (!$top) {
            return;
        }

        CombinationRecommendationSnapshot::create([
            'student_id'              => $student->id,
            'term'                    => $term,
            'form_level'              => $form ?? $student->studentProfile?->form_level,
            'recommended_combination' => str_replace(' Path', '', $top['path']),
            'score'                   => $top['score'],
            'all_scores'              => collect($paths)->mapWithKeys(fn ($p) => [
                str_replace(' Path', '', $p['path']) => $p['score'],
            ])->all(),
        ]);
    }

    private function latestPerSubjectFrom(Collection $results): Collection
    {
        $formRank = ['Form 1' => 1, 'Form 2' => 2, 'Form 3' => 3, 'Form 4' => 4];
        $termRank = ['Term 1' => 1, 'Term 2' => 2, 'Term 3' => 3];

        return $results
            ->groupBy('subject_id')
            ->map(fn ($rows) => $rows->sortByDesc(
                fn ($r) => ($formRank[$r->form_level] ?? 0) * 1000
                         + ($termRank[$r->term] ?? 0) * 100
                         + ($r->updated_at?->timestamp ?? 0)
            )->first())
            ->filter()
            ->values();
    }

    private function overallTrend(Collection $results): array
    {
        $byPeriod = $results->sortBy(fn ($r) => ($r->form_level ?? '') . ($r->term ?? ''))
            ->groupBy(fn ($r) => ($r->form_level ?? '') . '|' . ($r->term ?? ''));
        $keys = $byPeriod->keys()->values();

        if ($keys->count() < 2) {
            return ['label' => 'Insufficient data', 'direction' => 'stable'];
        }

        $prev = $byPeriod->get($keys[$keys->count() - 2])->avg('score');
        $last = $byPeriod->get($keys->last())->avg('score');
        $delta = $last - $prev;

        return [
            'label'     => $delta > 3 ? 'Improving' : ($delta < -3 ? 'Declining' : 'Stable'),
            'direction' => $delta > 3 ? 'up' : ($delta < -3 ? 'down' : 'stable'),
        ];
    }

    private function readinessLabel(float $score): string
    {
        return match (true) {
            $score >= self::READY_MIN => 'READY',
            $score >= self::PARTIAL_MIN => 'PARTIALLY READY',
            default => 'NOT YET READY',
        };
    }

    private function readinessFromGrades(array $gradeScores): string
    {
        if (empty($gradeScores)) {
            return 'NOT YET READY';
        }

        $min = min($gradeScores);
        $avg = array_sum($gradeScores) / count($gradeScores);

        return match (true) {
            $min >= self::READY_MIN && $avg >= self::READY_MIN => 'READY',
            $min >= self::PARTIAL_MIN => 'PARTIALLY READY',
            default => 'NOT YET READY',
        };
    }

    private function findPeerScore(Collection $peerScores, string $subject): ?float
    {
        $needle = strtolower($subject);
        foreach ($peerScores as $name => $score) {
            if ($name === $needle || str_contains($name, $needle)) {
                return (float) $score;
            }
        }

        return null;
    }

    private function simulatePathsWithResults(Collection $results, ?AssessmentAnalysis $analysis): array
    {
        $subjectScores = $results->groupBy(fn ($r) => strtolower(trim($r->subject?->name ?? '')))
            ->map(fn ($rows) => round((float) $rows->avg('score'), 1));

        $suitability = $analysis?->subject_suitability_scores ?? [];
        $groupScores = collect($suitability['groups'] ?? [])->keyBy('label');
        $paths = [];

        foreach (IntelligentAnalysisService::MSCE_PATHS as $pathName => $subjects) {
            $gradeScores = [];
            foreach ($subjects as $subName) {
                $score = $this->findPeerScore($subjectScores, $subName);
                if ($score !== null) {
                    $gradeScores[$subName] = $score;
                }
            }

            $gradeAvg = !empty($gradeScores) ? array_sum($gradeScores) / count($gradeScores) : null;
            $groupLabel = match ($pathName) {
                'Science Path'    => 'Science Combination',
                'Humanities Path' => 'Humanities Combination',
                default           => 'Humanities Combination',
            };
            $interestScore = (float) ($groupScores->get($groupLabel)['score'] ?? 50);
            $finalScore = $gradeAvg !== null
                ? round(min(98, $gradeAvg * self::GRADE_WEIGHT + $interestScore * self::INTEREST_WEIGHT), 1)
                : 0;

            $paths[] = [
                'path'         => $pathName,
                'subjects'     => $subjects,
                'grade_scores' => $gradeScores,
                'score'        => $finalScore,
            ];
        }

        usort($paths, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $paths;
    }
}
