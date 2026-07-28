<?php
$files = [
    'app/Services/IntelligentAnalysisService.php' => [
        [
            'old' => "use App\\Models\\{\n    AssessmentAnalysis,",
            'new' => "use App\\Models\\{\n    AcademicResult,\n    AssessmentAnalysis,",
        ],
        [
            'old' => "        \$averages = \$this->form12Results(\$student)\n            ->groupBy('subject_id')\n            ->map(fn (\$rows) => [\n                'name'  => \$rows->first()->subject?->name ?? 'Unknown',\n                'score' => round(\$rows->avg('score'), 1),\n            ])",
            'new' => "        \$averages = \$this->currentResults(\$student)\n            ->map(fn (\$r) => [\n                'name'  => \$r->subject?->name ?? 'Unknown',\n                'score' => round((float) \$r->score, 1),\n            ])",
        ],
        [
            'old' => "        \$subjectAvgScores = \$this->form12Results(\$student)\n            ->groupBy('subject_id')\n            ->map(fn (\$rows) => \$rows->avg('score'))\n            ->map(fn (\$v) => round((float) \$v, 2));",
            'new' => "        \$subjectAvgScores = \$this->currentResults(\$student)\n            ->mapWithKeys(fn (\$r) => [\$r->subject_id => round((float) \$r->score, 2)]);",
        ],
        [
            'old' => "        \$subjectScores = \$this->form12Results(\$student)\n            ->groupBy(fn (\$r) => strtolower(trim(\$r->subject?->name ?? '')))\n            ->map(fn (\$rows) => round((float) \$rows->avg('score'), 1));",
            'new' => "        \$subjectScores = \$this->currentResults(\$student)\n            ->mapWithKeys(fn (\$r) => [\n                strtolower(trim(\$r->subject?->name ?? '')) => round((float) \$r->score, 1),\n            ]);",
        ],
        [
            'old' => "    /** Teacher-uploaded grades across all forms for combination analysis. */\n    private function form12Results(User \$student): Collection\n    {\n        return \$student->academicResults()\n            ->with('subject')\n            ->get();\n    }",
            'new' => "    /** All historical teacher-uploaded grades (trends, history). */\n    private function form12Results(User \$student): Collection\n    {\n        return \$student->academicResults()\n            ->with('subject')\n            ->get();\n    }\n\n    /** Current grades — latest upload per subject. */\n    private function currentResults(User \$student): Collection\n    {\n        return AcademicResult::latestPerSubject(\$student);\n    }",
        ],
    ],
    'app/Services/RecommendationEngine.php' => [
        [
            'old' => "        // Student's subject IDs from academic results\n        \$studentSubjectIds = \$student->academicResults()->pluck('subject_id')->unique();\n\n        // Average score per subject\n        \$subjectAvgScores = \$student->academicResults()\n            ->selectRaw('subject_id, AVG(score) as avg_score')\n            ->groupBy('subject_id')\n            ->pluck('avg_score', 'subject_id');",
            'new' => "        \$latestGrades = \\App\\Models\\AcademicResult::latestPerSubject(\$student);\n        \$studentSubjectIds = \$latestGrades->pluck('subject_id');\n        \$subjectAvgScores = \$latestGrades->mapWithKeys(fn (\$r) => [\$r->subject_id => (float) \$r->score]);",
        ],
    ],
    'app/Services/AcademicProgressService.php' => [
        [
            'old' => "    public function getStrengthsWeaknesses(?Collection \$results = null): array\n    {\n        if (!\$results || \$results->isEmpty()) {\n            return ['strengths' => [], 'weaknesses' => []];\n        }\n\n        \$averages = \$results->groupBy('subject_id')->map(fn (\$rows) => [\n            'name'  => \$rows->first()->subject?->name ?? 'Unknown',\n            'score' => round((float) \$rows->avg('score'), 1),\n        ])->sortByDesc('score')->values();",
            'new' => "    public function getStrengthsWeaknesses(?Collection \$results = null): array\n    {\n        if (!\$results || \$results->isEmpty()) {\n            return ['strengths' => [], 'weaknesses' => []];\n        }\n\n        \$averages = \$this->latestPerSubjectFrom(\$results)->map(fn (\$r) => [\n            'name'  => \$r->subject?->name ?? 'Unknown',\n            'score' => round((float) \$r->score, 1),\n        ])->sortByDesc('score')->values();",
        ],
    ],
];

chdir(__DIR__ . '/..');
foreach ($files as $rel => $replacements) {
    $path = getcwd() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    $content = file_get_contents($path);
    foreach ($replacements as $r) {
        if (!str_contains($content, $r['old'])) {
            fwrite(STDERR, "WARN: pattern not found in $rel\n");
            continue;
        }
        $content = str_replace($r['old'], $r['new'], $content);
    }
    file_put_contents($path, $content);
    echo "Patched $rel\n";
}
