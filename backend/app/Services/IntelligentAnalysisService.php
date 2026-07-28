<?php

namespace App\Services;

use App\Models\{
    AcademicResult,
    AssessmentAnalysis,
    AssessmentAttempt,
    Subject,
    SubjectCombination,
    User,
};
use Illuminate\Support\Collection;

class IntelligentAnalysisService
{
  private const INTEREST_KEYWORDS = [
        'math' => ['mathematics', 'math', 'numbers', 'data', 'logic', 'analyst'],
        'science' => ['science', 'laboratory', 'experiment', 'biology', 'chemistry', 'physics', 'research'],
        'humanities' => ['reading', 'writing', 'history', 'debate', 'english', 'social', 'journalism', 'law'],
        'practical' => ['practical', 'hands-on', 'building', 'fixing', 'tools', 'workshop', 'physical', 'outdoor'],
    ];

    private const COMBINATION_GROUPS = [
        'Science Combination' => ['Pure Sciences', 'Physical Sciences', 'Biological Sciences', 'Computer Science', 'Education Sciences'],
        'Commercial Combination' => ['Commerce'],
        'Humanities Combination' => ['Arts & Humanities'],
        'Agriculture Combination' => ['Agriculture & Environment', 'Biological Sciences'],
    ];

    public const MSCE_PATHS = [
        'Science Path'     => ['Mathematics', 'Physics', 'Chemistry', 'Biology'],
        'Humanities Path'  => ['English', 'History', 'Geography', 'Social Studies'],
    ];

    public function analyzeAndStore(AssessmentAttempt $attempt, User $student): AssessmentAnalysis
    {
        $interestScores = $this->analyzeInterests($attempt);
        $strengthData = $this->detectStrengthsAndWeaknesses($student);
        $predicted = $this->predictPerformance($student);
        $savedPrefs = $this->getSavedSubjectPrefs($student);
        $suitability = $this->scoreSubjectCombinations(
            $student,
            $attempt,
            $interestScores,
            $strengthData['strengths'],
            $strengthData['weaknesses'],
            $savedPrefs
        );
        $topCombo = $this->resolveTopCombination($suitability);
        $confidence = $this->calculateConfidence($attempt, $student);
        $conflicts = $this->detectSubjectConflicts($student, $suitability);
        $explanation = $this->buildExplanation(
            $topCombo,
            $suitability,
            $interestScores,
            $strengthData['strengths'],
            $student
        );

        return AssessmentAnalysis::updateOrCreate(
            ['attempt_id' => $attempt->id],
            [
                'student_id'                 => $student->id,
                'interest_scores'            => $interestScores,
                'strengths'                  => $strengthData['strengths'],
                'weaknesses'                 => $strengthData['weaknesses'],
                'predicted_performance'      => $predicted,
                'subject_suitability_scores' => $suitability,
                'top_combination'            => $topCombo,
                'confidence_score'           => $confidence['score'],
                'confidence_level'           => $confidence['level'],
                'explanation'                => $explanation,
                'subject_conflicts'          => $conflicts,
            ]
        );
    }

    public function analyzeInterests(AssessmentAttempt $attempt): array
    {
        $scores = [
            'Math Interest'        => 0.0,
            'Science Interest'     => 0.0,
            'Humanities Interest'  => 0.0,
            'Practical Interest'   => 0.0,
        ];
        $weights = [
            'Math Interest'        => 0.0,
            'Science Interest'     => 0.0,
            'Humanities Interest'  => 0.0,
            'Practical Interest'   => 0.0,
        ];

        $responses = $attempt->responses()->with(['question', 'option'])->get();

        foreach ($responses as $response) {
            $question = $response->question;
            if (!$question) {
                continue;
            }

            $textBlob = strtolower(
                ($response->option?->option_text ?? '') . ' ' .
                ($response->text_response ?? '') . ' ' .
                ($question->question_text ?? '')
            );

            if ($question->type === 'scale' && $response->scale_value) {
                $scaleWeight = ($response->scale_value / 5) * 100;
                foreach (self::INTEREST_KEYWORDS as $key => $keywords) {
                    if ($this->textMatchesKeywords($textBlob, $keywords)) {
                        $label = $this->interestLabel($key);
                        $weights[$label] += 1;
                        $scores[$label] += $scaleWeight;
                    }
                }
                continue;
            }

            if ($response->option?->mapped_career_category) {
                $category = $response->option->mapped_career_category;
                $this->applyCategoryInterest($scores, $weights, $category, 100);
            }

            foreach (self::INTEREST_KEYWORDS as $key => $keywords) {
                if ($this->textMatchesKeywords($textBlob, $keywords)) {
                    $label = $this->interestLabel($key);
                    $weights[$label] += 1;
                    $scores[$label] += 85;
                }
            }
        }

        return collect($scores)->map(function ($total, $label) use ($weights) {
            $count = max(1, $weights[$label]);
            return (int) min(98, round($total / $count));
        })->sortDesc()->toArray();
    }

    public function detectStrengthsAndWeaknesses(User $student): array
    {
        $averages = $this->currentResults($student)
            ->map(fn ($r) => [
                'name'  => $r->subject?->name ?? 'Unknown',
                'score' => round((float) $r->score, 1),
            ])
            ->sortByDesc('score')
            ->values();

        if ($averages->isEmpty()) {
            return ['strengths' => [], 'weaknesses' => []];
        }

        $strengthThreshold = max(70, $averages->avg('score') + 8);
        $weaknessThreshold = min(55, $averages->avg('score') - 8);

        return [
            'strengths'  => $averages->where('score', '>=', $strengthThreshold)->pluck('name')->take(5)->values()->all(),
            'weaknesses' => $averages->where('score', '<=', $weaknessThreshold)->pluck('name')->take(5)->values()->all(),
        ];
    }

    public function predictPerformance(User $student): array
    {
        return $this->form12Results($student)
            ->groupBy('subject_id')
            ->map(function ($rows) {
                $avg = (float) $rows->avg('score');
                $trend = $rows->sortBy('term')->pluck('score')->values();
                $projected = $avg;

                if ($trend->count() >= 2) {
                    $delta = $trend->last() - $trend->first();
                    $projected = min(100, max(0, $avg + ($delta * 0.35)));
                }

                return [
                    'subject' => $rows->first()->subject?->name ?? 'Unknown',
                    'grade'   => $this->scoreToMsceGrade($projected),
                    'score'   => round($projected, 1),
                ];
            })
            ->sortByDesc('score')
            ->values()
            ->take(8)
            ->all();
    }

    public function scoreSubjectCombinations(
        User $student,
        AssessmentAttempt $attempt,
        array $interestScores,
        array $strengths,
        array $weaknesses,
        array $savedPrefs = []
    ): array {
        $categoryScores = $attempt->tallyCareerCategories();
        $totalVotes = max(1, array_sum($categoryScores));

        $prefsText = strtolower(implode(' ', array_filter([
            $savedPrefs['career_interests'] ?? '',
            $savedPrefs['personal_interests'] ?? '',
            $savedPrefs['learning_preference'] ?? '',
            $savedPrefs['additional_info'] ?? '',
            is_array($savedPrefs['preferred_subjects'] ?? null) ? implode(' ', $savedPrefs['preferred_subjects']) : '',
            is_array($savedPrefs['strengths'] ?? null) ? implode(' ', $savedPrefs['strengths']) : '',
        ])));
        $prefsBoosts = $this->keywordInterestFromText($prefsText);

        $subjectAvgScores = $this->currentResults($student)
            ->mapWithKeys(fn ($r) => [$r->subject_id => round((float) $r->score, 2)]);

        $studentSubjectIds = $subjectAvgScores->keys();
        $strengthSet = collect($strengths)->map(fn ($s) => strtolower($s));
        $weaknessSet = collect($weaknesses)->map(fn ($s) => strtolower($s));

        $combinations = SubjectCombination::with('subjects')->get();
        $grouped = [];
        $details = [];

        foreach ($combinations as $combo) {
            $comboSubjects = $combo->subjects;
            $comboSubjectIds = $comboSubjects->pluck('id');
            $groupLabel = $this->combinationGroupLabel($combo->name);

            $interestScore = $this->combinationInterestScore($groupLabel, $interestScores, $categoryScores, $totalVotes);
            $academicScore = $this->combinationAcademicScore($comboSubjects, $subjectAvgScores, $studentSubjectIds);
            $strengthBonus = $this->strengthWeaknessAdjustment($comboSubjects, $strengthSet, $weaknessSet);
            $pathName = match ($groupLabel) {
                'Science Combination' => 'Science Path',
                'Humanities Combination' => 'Humanities Path',
                'Commercial Combination' => 'Commercial Path',
                'Agriculture Combination' => 'Agriculture Path',
                default => null,
            };
            $prefsBoost = $pathName ? (float) ($prefsBoosts[$pathName] ?? 0) : 0;
            if ($prefsBoost > 0) {
                $interestScore = min(98, $interestScore * 0.55 + $prefsBoost * 0.45);
            }
            $final = (float) min(98, round($academicScore * 0.50 + $interestScore * 0.35 + $strengthBonus * 0.15, 2));

            $grouped[$groupLabel] = max($grouped[$groupLabel] ?? 0, $final);

            $details[$combo->name] = [
                'combination' => $combo->name,
                'group'       => $groupLabel,
                'score'       => $final,
                'id'          => $combo->id,
            ];
        }

        arsort($grouped);

        $result = [];
        foreach ($grouped as $label => $score) {
            $result[] = [
                'label' => $label,
                'score' => round($score, 1),
            ];
        }

        return [
            'groups'   => $result,
            'details'  => collect($details ?? [])->sortByDesc('score')->values()->all(),
        ];
    }

    public function calculateConfidence(AssessmentAttempt $attempt, User $student): array
    {
        $responseCount = $attempt->responses()->count();
        $mcCount = $attempt->responses()->whereNotNull('option_id')->count();
        $gradeCount = $student->academicResults()->distinct('subject_id')->count('subject_id');

        $dataPoints = min(100, ($responseCount * 4) + ($gradeCount * 6) + ($mcCount * 2));
        $score = (float) min(98, round(45 + ($dataPoints * 0.45), 2));

        $level = match (true) {
            $score >= 90 => 'very_high',
            $score >= 75 => 'high',
            $score >= 60 => 'moderate',
            default      => 'low',
        };

        return ['score' => $score, 'level' => $level];
    }

    public function detectSubjectConflicts(User $student, array $suitability): array
    {
        $currentSubjects = $this->form12Results($student)
            ->pluck('subject.name', 'subject.id')
            ->filter();

        if ($currentSubjects->count() < 3) {
            return [];
        }

        $subjectNames = $currentSubjects->values()->all();
        $topGroup = $suitability['groups'][0]['label'] ?? null;
        $topScore = $suitability['groups'][0]['score'] ?? 0;

        $categories = Subject::whereIn('id', $currentSubjects->keys())
            ->pluck('category')
            ->unique()
            ->values();

        $isMixed = $categories->count() >= 3;
        $hasWeakAlignment = $topScore < 65;

        if (!$isMixed && !$hasWeakAlignment) {
            return [];
        }

        return [[
            'selected_subjects' => $subjectNames,
            'warning'           => 'Your current subjects do not form a strong subject combination based on your academic performance and interests.',
            'suggested'         => $topGroup ?? 'Science Combination',
            'suggested_score'   => $topScore,
        ]];
    }

    public function buildExplanation(
        ?array $topCombo,
        array $suitability,
        array $interestScores,
        array $strengths,
        User $student
    ): string {
        $label = $topCombo['label'] ?? ($suitability['groups'][0]['label'] ?? 'Science Combination');
        $score = $topCombo['score'] ?? ($suitability['groups'][0]['score'] ?? 0);

        $gradeCount = $this->form12Results($student)->pluck('subject_id')->unique()->count();
        $strengthText = !empty($strengths)
            ? 'Your Form 1–2 grades are strong in ' . implode(', ', array_slice($strengths, 0, 3)) . '.'
            : ($gradeCount > 0
                ? 'Your teacher-uploaded grades support this direction.'
                : 'Complete more assessments and wait for teacher grade uploads for stronger analysis.');

        $topInterests = collect($interestScores)->sortDesc()->take(2)->keys()->implode(' and ');
        $interestLine = $topInterests
            ? "Your self-assessment shows interest in {$topInterests}."
            : '';

        return "{$label} is recommended ({$score}% match from grades and assessment). {$strengthText} {$interestLine}";
    }

    /**
     * Analyse the four MSCE paths using Form 1–2 grades and assessment data.
     * @param array $savedPrefs Optional subject-combo preferences from the form (career_interests, personal_interests, etc.)
     */
    public function analyzeMscePaths(User $student, ?AssessmentAnalysis $analysis = null, array $savedPrefs = []): array
    {
        $subjectScores = $this->currentResults($student)
            ->mapWithKeys(fn ($r) => [
                strtolower(trim($r->subject?->name ?? '')) => round((float) $r->score, 1),
            ]);

        $suitability   = $analysis?->subject_suitability_scores ?? [];
        $groupScores   = collect($suitability['groups'] ?? [])->keyBy('label');
        $strengths     = $analysis?->strengths ?? [];
        $weaknesses    = $analysis?->weaknesses ?? [];

        // Keyword-based interest scores derived from the student's form preferences
        $prefsText = strtolower(implode(' ', array_filter([
            $savedPrefs['career_interests']   ?? '',
            $savedPrefs['personal_interests'] ?? '',
            $savedPrefs['additional_info']    ?? '',
            is_array($savedPrefs['preferred_subjects'] ?? null) ? implode(' ', $savedPrefs['preferred_subjects']) : '',
        ])));
        $prefsInterestBoosts = $this->keywordInterestFromText($prefsText);

        $paths = [];
        foreach (self::MSCE_PATHS as $pathName => $subjects) {
            $gradeScores = [];
            foreach ($subjects as $subName) {
                $score = $this->findSubjectScore($subjectScores, $subName);
                if ($score !== null) {
                    $gradeScores[$subName] = $score;
                }
            }

            $gradeAvg = !empty($gradeScores)
                ? round(array_sum($gradeScores) / count($gradeScores), 1)
                : null;

            $groupLabel = match ($pathName) {
                'Science Path'     => 'Science Combination',
                'Humanities Path'  => 'Humanities Combination',
                'Commercial Path'  => 'Commercial Combination',
                'Agriculture Path' => 'Agriculture Combination',
                default            => 'Humanities Combination',
            };

            $interestScoreRaw = $analysis?->interest_scores ?? [];
            $selfInterest     = $this->pathSelfInterestScore($pathName, $interestScoreRaw);
            $careerAlignment  = $this->pathCareerAlignment($pathName, $interestScoreRaw, $groupScores->get($groupLabel, []));

            // Blend in prefs-based interest if the form text had meaningful keywords
            if (!empty($prefsInterestBoosts)) {
                $prefsBoost = $prefsInterestBoosts[$pathName] ?? 0;
                if ($prefsBoost > 0) {
                    // Weight: 60% original interest, 40% from form preferences
                    $selfInterest = min(98.0, $selfInterest * 0.6 + $prefsBoost * 0.4);
                }
            }

            $hasGrades         = $gradeAvg !== null;
            $gradeComponent    = $hasGrades ? ($gradeAvg * 0.70) : 0;
            $interestComponent = $selfInterest * 0.20;
            $careerComponent   = $careerAlignment * 0.10;
            $finalScore = $hasGrades || $selfInterest > 35
                ? round(min(98, $gradeComponent + $interestComponent + $careerComponent), 1)
                : 0;

            $notReadyReasons = $this->buildNotReadyReasons($subjects, $gradeScores, $weaknesses);
            $whyReasons      = $this->buildWhyReasons($subjects, $gradeScores, $strengths, $selfInterest, $careerAlignment);
            $status          = $this->pathStatus($finalScore, $notReadyReasons);

            $paths[] = [
                'path'              => $pathName,
                'subjects'          => $subjects,
                'grade_scores'      => $gradeScores,
                'score'             => $finalScore,
                'has_grade_data'    => $hasGrades,
                'grade_avg'         => $gradeAvg,
                'interest_score'    => round($selfInterest, 1),
                'career_alignment'  => round($careerAlignment, 1),
                'explanation'       => $this->buildPathExplanation($pathName, $gradeScores, $strengths, $selfInterest, $gradeAvg),
                'improvements'      => $this->pathImprovements($subjects, $gradeScores, $weaknesses),
                'why_reasons'       => $whyReasons,
                'not_ready_reasons' => $notReadyReasons,
                'suggested_actions' => $this->buildSuggestedActions($notReadyReasons, $subjects),
                'status'            => $status,
            ];
        }

        usort($paths, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $paths;
    }

    public function getRecommendationHistory(User $student): array
    {
        return $student->assessmentAttempts()
            ->where('status', 'completed')
            ->with('analysis')
            ->orderBy('completed_at')
            ->get()
            ->map(function ($attempt, $index) {
                $top = $attempt->analysis?->top_combination;
                $groups = $attempt->analysis?->subject_suitability_scores['groups'] ?? [];

                return [
                    'assessment'       => 'Assessment ' . ($index + 1),
                    'date'             => $attempt->completed_at?->format('d M Y'),
                    'combination'      => $top['label'] ?? ($groups[0]['label'] ?? '—'),
                    'score'            => $top['score'] ?? ($groups[0]['score'] ?? 0),
                    'confidence'       => $attempt->analysis?->confidence_score,
                    'confidence_level' => $attempt->analysis?->confidence_level ?? 'low',
                    'reason'           => $this->historyReason($attempt->analysis),
                ];
            })
            ->values()
            ->all();
    }

    public function combinationReason(SubjectCombination $combo, array $suitability, array $interestScores, array $strengths, array $weaknesses): string
    {
        $detail = collect($suitability['details'] ?? [])->firstWhere('combination', $combo->name);
        $score = $detail['score'] ?? 0;
        $group = $detail['group'] ?? $this->combinationGroupLabel($combo->name);

        $strengthHits = $combo->subjects
            ->pluck('name')
            ->filter(fn ($name) => in_array($name, $strengths))
            ->take(3)
            ->implode(', ');

        $weakHits = $combo->subjects
            ->pluck('name')
            ->filter(fn ($name) => in_array($name, $weaknesses))
            ->take(2)
            ->all();

        $parts = [];
        if ($score > 0) {
            $parts[] = "Calculated match: {$score}% from your Form 1–2 grades and self-assessment.";
        }
        if ($strengthHits) {
            $parts[] = "You perform well in {$strengthHits} according to teacher records.";
        }
        if (!empty($weakHits)) {
            $parts[] = 'Consider improving ' . implode(' and ', $weakHits) . ' before choosing this combination.';
        }
        if (empty($parts)) {
            $parts[] = 'Complete your assessment and ensure teachers have uploaded Form 1–2 grades.';
        }

        return implode(' ', $parts);
    }

    private function resolveTopCombination(array $suitability): ?array
    {
        $top = $suitability['groups'][0] ?? null;
        if (!$top) {
            return null;
        }

        $detail = collect($suitability['details'] ?? [])->firstWhere('group', $top['label']);

        return [
            'label'       => $top['label'],
            'score'       => $top['score'],
            'combination' => $detail['combination'] ?? $top['label'],
        ];
    }

    private function combinationGroupLabel(string $name): string
    {
        foreach (self::COMBINATION_GROUPS as $group => $names) {
            if (in_array($name, $names, true)) {
                return $group;
            }
        }

        if (str_contains(strtolower($name), 'commerce')) {
            return 'Commercial Combination';
        }
        if (str_contains(strtolower($name), 'agri')) {
            return 'Agriculture Combination';
        }
        if (str_contains(strtolower($name), 'arts') || str_contains(strtolower($name), 'humanities')) {
            return 'Humanities Combination';
        }

        return 'Science Combination';
    }

    private function combinationInterestScore(string $group, array $interests, array $categories, int $totalVotes): float
    {
        $map = [
            'Science Combination'     => ['Science Interest', 'Math Interest', 'Technology', 'Health', 'Engineering'],
            'Commercial Combination'  => ['Math Interest', 'Business'],
            'Humanities Combination'  => ['Humanities Interest', 'Arts', 'Law', 'Education'],
            'Agriculture Combination' => ['Practical Interest', 'Science Interest', 'Agriculture'],
        ];

        $interestKeys = $map[$group] ?? [];
        $interestPart = 0.0;
        foreach ($interestKeys as $key) {
            if (isset($interests[$key])) {
                $interestPart = max($interestPart, (float) $interests[$key]);
            }
        }

        $categoryPart = 0.0;
        foreach ($categories as $category => $votes) {
            if (in_array($category, $interestKeys, true)) {
                $categoryPart = max($categoryPart, ($votes / $totalVotes) * 100);
            }
        }

        return max($interestPart, $categoryPart, 35);
    }

    private function combinationAcademicScore(Collection $subjects, Collection $avgScores, Collection $studentSubjectIds): float
    {
        if ($subjects->isEmpty()) {
            return 40.0;
        }

        $matched = $subjects->filter(fn ($s) => $studentSubjectIds->contains($s->id));
        $coverage = ($matched->count() / $subjects->count()) * 55;

        $performance = $matched->map(fn ($s) => (float) ($avgScores->get($s->id, 0)))->avg() ?? 0;

        return min(98, $coverage + ($performance * 0.43));
    }

    /** All historical teacher-uploaded grades (trends, history). */
    private function form12Results(User $student): Collection
    {
        return $student->academicResults()
            ->with('subject')
            ->get();
    }


    private function getSavedSubjectPrefs(User $student): array
    {
        try {
            $skills = json_decode($student->studentProfile?->skills ?? '{}', true, 512, JSON_THROW_ON_ERROR);
            return $skills['subject_combo'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }
    /** Current grades — latest upload per subject. */
    private function currentResults(User $student): Collection
    {
        return AcademicResult::latestPerSubject($student);
    }

    private function strengthWeaknessAdjustment(Collection $subjects, Collection $strengths, Collection $weaknesses): float
    {
        $names = $subjects->pluck('name')->map(fn ($n) => strtolower($n));
        $strengthHits = $names->filter(fn ($n) => $strengths->contains($n))->count();
        $weaknessHits = $names->filter(fn ($n) => $weaknesses->contains($n))->count();

        return min(98, 50 + ($strengthHits * 12) - ($weaknessHits * 15));
    }

    private function applyCategoryInterest(array &$scores, array &$weights, string $category, float $value): void
    {
        $map = [
            'Technology'  => 'Science Interest',
            'Health'      => 'Science Interest',
            'Engineering' => 'Practical Interest',
            'Agriculture' => 'Practical Interest',
            'Arts'        => 'Humanities Interest',
            'Law'         => 'Humanities Interest',
            'Education'   => 'Humanities Interest',
            'Business'    => 'Math Interest',
        ];

        $label = $map[$category] ?? 'Humanities Interest';
        $weights[$label] += 1;
        $scores[$label] += $value;
    }

    private function interestLabel(string $key): string
    {
        return match ($key) {
            'math'       => 'Math Interest',
            'science'    => 'Science Interest',
            'humanities' => 'Humanities Interest',
            'practical'  => 'Practical Interest',
            default      => 'Humanities Interest',
        };
    }

    private function textMatchesKeywords(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    private function scoreToMsceGrade(float $score): string
    {
        return match (true) {
            $score >= 85 => 'A',
            $score >= 75 => 'B+',
            $score >= 65 => 'B',
            $score >= 55 => 'C+',
            $score >= 45 => 'C',
            default      => 'D',
        };
    }

    private function findSubjectScore(Collection $scores, string $subjectName): ?float
    {
        $needle = strtolower($subjectName);
        foreach ($scores as $name => $score) {
            if ($name === $needle || str_contains($name, $needle) || str_contains($needle, $name)) {
                return (float) $score;
            }
        }

        return null;
    }

    private function buildPathExplanation(
        string $pathName,
        array $gradeScores,
        array $strengths,
        float $interestScore,
        ?float $gradeAvg
    ): string {
        $parts = ["{$pathName} is evaluated from your Form 1–2 teacher grades and self-assessment."];

        if ($gradeAvg !== null) {
            $parts[] = 'Average grade in path subjects: ' . round($gradeAvg, 1) . '%.';
        } else {
            $parts[] = 'No Form 1–2 grades found yet for these subjects — ask your teacher to upload results.';
        }

        $overlap = array_intersect($strengths, array_keys($gradeScores));
        if (!empty($overlap)) {
            $parts[] = 'Strengths: ' . implode(', ', array_slice($overlap, 0, 3)) . '.';
        }

        if ($interestScore > 0) {
            $parts[] = 'Your career assessment supports this direction.';
        }

        return implode(' ', $parts);
    }

    /**
     * Derive per-path interest boost scores from freeform preference text.
     * Returns [pathName => score 0-100] only when meaningful keywords are found.
     */
    private function keywordInterestFromText(string $text): array
    {
        if (strlen(trim($text)) < 3) {
            return [];
        }

        $pathKeywords = [
            'Science Path'     => ['science', 'physics', 'chemistry', 'biology', 'mathematics', 'math', 'maths',
                                   'medicine', 'doctor', 'engineer', 'engineering', 'laboratory', 'lab', 'research',
                                   'technology', 'nursing', 'pharmacist', 'computer', 'programming'],
            'Humanities Path'  => ['english', 'history', 'geography', 'social', 'law', 'lawyer', 'journalism',
                                   'writing', 'literature', 'arts', 'teacher', 'education', 'sociology',
                                   'political', 'media', 'communication', 'language', 'philosophy'],
            'Commercial Path'  => ['accounting', 'business', 'commerce', 'economics', 'finance', 'banking',
                                   'entrepreneur', 'marketing', 'trade', 'management'],
            'Agriculture Path' => ['agriculture', 'farming', 'crop', 'livestock', 'agronomy', 'environment',
                                   'natural resources', 'food security', 'rural'],
        ];

        $boosts = [];
        foreach ($pathKeywords as $pathName => $keywords) {
            $hits = 0;
            foreach ($keywords as $kw) {
                if (str_contains($text, $kw)) {
                    $hits++;
                }
            }
            if ($hits > 0) {
                // Scale: 1 hit = 55, 2 hits = 70, 3+ hits = 85
                $boosts[$pathName] = min(90.0, 45.0 + ($hits * 15.0));
            }
        }

        // Only return boosts if at least one path got a meaningful signal
        return !empty($boosts) ? $boosts : [];
    }

    private function pathImprovements(array $subjects, array $gradeScores, array $weaknesses): array
    {
        $improve = [];
        foreach ($subjects as $sub) {
            if (in_array($sub, $weaknesses, true)) {
                $improve[] = $sub;
                continue;
            }
            if (isset($gradeScores[$sub]) && $gradeScores[$sub] < 55) {
                $improve[] = $sub;
            }
        }

        return array_values(array_unique($improve));
    }

    private function pathSelfInterestScore(string $pathName, array $interestScores): float
    {
        $map = [
            'Science Path'     => ['Science Interest', 'Math Interest'],
            'Humanities Path'  => ['Humanities Interest'],
            'Commercial Path'  => ['Math Interest'],
            'Agriculture Path' => ['Practical Interest', 'Science Interest'],
        ];

        $best = 35.0;
        foreach ($map[$pathName] ?? [] as $key) {
            if (isset($interestScores[$key])) {
                $best = max($best, (float) $interestScores[$key]);
            }
        }

        return min(98.0, $best);
    }

    private function pathCareerAlignment(string $pathName, array $interestScores, mixed $groupData): float
    {
        $groupScore = (float) ($groupData['score'] ?? 0);
        if ($groupScore > 0) {
            return min(98.0, max(35.0, $groupScore));
        }

        return $this->pathSelfInterestScore($pathName, $interestScores);
    }

    private function pathStatus(float $score, array $notReadyReasons): string
    {
        if (!empty($notReadyReasons)) {
            return $score >= 55 ? 'partial' : 'not_ready';
        }

        return match (true) {
            $score >= 70 => 'ready',
            $score >= 50 => 'partial',
            default      => 'not_ready',
        };
    }

    private function buildWhyReasons(
        array $subjects,
        array $gradeScores,
        array $strengths,
        float $interestScore,
        float $careerAlignment
    ): array {
        $reasons = [];

        foreach ($subjects as $sub) {
            if (isset($gradeScores[$sub])) {
                $s = $gradeScores[$sub];
                if ($s >= 80) {
                    $reasons[] = "Excellent {$sub} performance ({$s}%)";
                } elseif ($s >= 70) {
                    $reasons[] = "Good {$sub} results ({$s}%)";
                } elseif ($s >= 60) {
                    $reasons[] = "Satisfactory {$sub} performance ({$s}%)";
                }
            }
            if (in_array($sub, $strengths, true)) {
                $already = array_filter($reasons, fn ($r) => str_contains($r, $sub));
                if (empty($already)) {
                    $reasons[] = "Strong {$sub} understanding shown in teacher records";
                }
            }
        }

        if ($interestScore >= 65) {
            $reasons[] = "High self-assessment interest in this subject area";
        } elseif ($interestScore >= 45) {
            $reasons[] = "Some self-assessment interest in this area";
        }

        if ($careerAlignment >= 65) {
            $reasons[] = "Career interests align well with this combination";
        }

        return array_values(array_unique($reasons));
    }

    private function buildNotReadyReasons(array $subjects, array $gradeScores, array $weaknesses): array
    {
        $reasons = [];
        foreach ($subjects as $sub) {
            if (in_array($sub, $weaknesses, true)) {
                $s = $gradeScores[$sub] ?? null;
                $reasons[] = $s !== null
                    ? "{$sub} average ({$s}%) is below the recommended level"
                    : "{$sub} performance needs improvement";
            } elseif (isset($gradeScores[$sub]) && $gradeScores[$sub] < 55) {
                $reasons[] = "{$sub} performance ({$gradeScores[$sub]}%) needs improvement";
            }
        }

        return array_values(array_unique($reasons));
    }

    private function buildSuggestedActions(array $notReadyReasons, array $subjects): array
    {
        if (empty($notReadyReasons)) {
            return [];
        }

        $actions = [];
        foreach ($notReadyReasons as $reason) {
            if (preg_match('/^([A-Za-z\s]+?)\s+(?:average|performance)/', $reason, $m)) {
                $sub = trim($m[1]);
                $actions[] = "Attend {$sub} revision sessions or extra classes";
                $actions[] = "Ask your {$sub} teacher for additional support";
            }
        }

        $actions[] = "Book a counsellor session for academic guidance";
        $actions[] = "The system will re-evaluate automatically after new grades are uploaded";

        return array_slice(array_values(array_unique($actions)), 0, 5);
    }

    private function historyReason(?AssessmentAnalysis $analysis): string
    {
        if (!$analysis) {
            return 'Assessment completed';
        }

        $explanation = $analysis->explanation ?? '';
        if (!$explanation) {
            return 'Based on grades and self-assessment';
        }

        $sentences = preg_split('/(?<=[.!?])\s+/', $explanation, 3);
        $reason    = trim($sentences[1] ?? $sentences[0] ?? '');

        if (strlen($reason) > 90) {
            $reason = substr($reason, 0, 87) . '...';
        }

        return $reason ?: 'Based on grades and self-assessment';
    }
}

