<?php
$base = 'c:/laragon/www/SMART-GUIDANCE-TOOL-main/backend';

$p = $base . '/app/Http/Controllers/StudentController.php';
$t = file_get_contents($p);
$t = preg_replace('/\s*\/\*\* Career Roadmap Planner.*?\n    \}\n\n\}/s', "\n}", $t);
$t = str_replace(
    "        \$this->refreshAnalysisIfNeeded(\$user, \$latestAttempt);\n        \$this->ensureRecommendationsGenerated(\$user, \$latestAttempt);\n\n        \$assessmentsCompleted",
    "        \$assessmentsCompleted",
    $t
);
$oldSave = "        return redirect(\$redirectUrl)\n            ->with('success', \$action === 'analyze'";
$newSave = "        if (\$action === 'analyze') {\n            \$attempt = AssessmentAttempt::where('student_id', \$user->id)\n                ->where('status', 'completed')\n                ->latest('completed_at')\n                ->first();\n            if (\$attempt) {\n                try {\n                    \$this->intelligence->analyzeAndStore(\$attempt, \$user);\n                    \$this->recommendationEngine->generate(\$attempt->fresh());\n                } catch (\\Throwable \$e) {\n                    Log::error('Preference re-analysis failed', ['error' => \$e->getMessage()]);\n                }\n            }\n        }\n\n        return redirect(\$redirectUrl)\n            ->with('success', \$action === 'analyze'";
if (strpos($t, 'Preference re-analysis failed') === false) { $t = str_replace($oldSave, $newSave, $t); }
$t = str_replace(
    "        \$combinationReport = null;\n        try {\n            \$combinationReport = \$this->academicProgress->getFullReport(\$user, \$analysis);\n        } catch (\\Throwable \$e) {\n            Log::error('combinationReport failed', ['error' => \$e->getMessage()]);\n        }\n\n        // Only show subjects",
    "        // Only show subjects",
    $t
);
$t = str_replace("'form12Subjects', 'mscePaths', 'targetForm', 'combinationReport',", "'form12Subjects', 'mscePaths', 'targetForm',", $t);
if (strpos($t, 'function voiceAsk') === false) {
    $voiceAsk = <<<'PHP'

    /** Voice mode: ask the AI assistant (no session_id required) */
    public function voiceAsk(Request $request): JsonResponse
    {
        set_time_limit(45);
        try {
            $user = Auth::user();
            $request->validate(['message' => ['required', 'string', 'max:500']]);
            $userMessage = $request->input('message');
            $session = $user->chatbotSessions()->latest()->first(fn ($s) => $s->isActive())
                ?? $user->chatbotSessions()->create();
            $history = $session->messages()->orderBy('created_at')->get(['sender', 'message'])->toArray();
            $session->messages()->create(['sender' => 'user', 'message' => $userMessage]);
            $botText = $this->chatbotService->respond($userMessage, $user, $history);
            $session->messages()->create(['sender' => 'bot', 'message' => $botText]);
            return response()->json(['reply' => $botText]);
        } catch (\Throwable $e) {
            Log::error('voiceAsk failed', ['error' => $e->getMessage()]);
            return response()->json(['reply' => "I'm having trouble right now. Please try again in a moment."]);
        }
    }

PHP;
    $t = str_replace('    /** Save a recommendation */', $voiceAsk . '    /** Save a recommendation */', $t);
}
file_put_contents($p, $t);
echo "StudentController ok\n";

$p = $base . '/app/Services/IntelligentAnalysisService.php';
$t = file_get_contents($p);
$t = str_replace(
    "    public const MSCE_PATHS = [\n        'Science Path'    => ['Mathematics', 'Physics', 'Chemistry', 'Biology'],\n        'Humanities Path' => ['English', 'History', 'Geography', 'Social Studies'],\n    ];",
    "    public const MSCE_PATHS = [\n        'Science Path'     => ['Mathematics', 'Physics', 'Chemistry', 'Biology'],\n        'Humanities Path'  => ['English', 'History', 'Geography', 'Social Studies'],\n        'Commercial Path'  => ['Accounting', 'Business Studies', 'Economics', 'Mathematics'],\n        'Agriculture Path' => ['Agriculture', 'Biology', 'Chemistry', 'Mathematics'],\n    ];",
    $t
);
if (strpos($t, 'getSavedSubjectPrefs') === false) {
    $t = str_replace(
        "        \$suitability = \$this->scoreSubjectCombinations(\n            \$student,\n            \$attempt,\n            \$interestScores,\n            \$strengthData['strengths'],\n            \$strengthData['weaknesses']\n        );",
        "        \$savedPrefs = \$this->getSavedSubjectPrefs(\$student);\n        \$suitability = \$this->scoreSubjectCombinations(\n            \$student,\n            \$attempt,\n            \$interestScores,\n            \$strengthData['strengths'],\n            \$strengthData['weaknesses'],\n            \$savedPrefs\n        );",
        $t
    );
    $helper = <<<'PHP'

    private function getSavedSubjectPrefs(User $student): array
    {
        try {
            $skills = json_decode($student->studentProfile?->skills ?? '{}', true, 512, JSON_THROW_ON_ERROR);
            return $skills['subject_combo'] ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

PHP;
    $t = str_replace('    /** Current grades', $helper . '    /** Current grades', $t);
}
$t = preg_replace(
    '/public function scoreSubjectCombinations\(\s*User \$student,\s*AssessmentAttempt \$attempt,\s*array \$interestScores,\s*array \$strengths,\s*array \$weaknesses\s*\): array \{/',
    'public function scoreSubjectCombinations(
        User $student,
        AssessmentAttempt $attempt,
        array $interestScores,
        array $strengths,
        array $weaknesses,
        array $savedPrefs = []
    ): array {',
    $t
);
$t = str_replace(
    "        \$categoryScores = \$attempt->tallyCareerCategories();\n        \$totalVotes = max(1, array_sum(\$categoryScores));",
    "        \$categoryScores = \$attempt->tallyCareerCategories();\n        \$totalVotes = max(1, array_sum(\$categoryScores));\n\n        \$prefsText = strtolower(implode(' ', array_filter([\n            \$savedPrefs['career_interests'] ?? '',\n            \$savedPrefs['personal_interests'] ?? '',\n            \$savedPrefs['learning_preference'] ?? '',\n            \$savedPrefs['additional_info'] ?? '',\n            is_array(\$savedPrefs['preferred_subjects'] ?? null) ? implode(' ', \$savedPrefs['preferred_subjects']) : '',\n            is_array(\$savedPrefs['strengths'] ?? null) ? implode(' ', \$savedPrefs['strengths']) : '',\n        ])));\n        \$prefsBoosts = \$this->keywordInterestFromText(\$prefsText);",
    $t
);
$t = str_replace(
    "            \$interestScore = \$this->combinationInterestScore(\$groupLabel, \$interestScores, \$categoryScores, \$totalVotes);\n            \$academicScore = \$this->combinationAcademicScore(\$comboSubjects, \$subjectAvgScores, \$studentSubjectIds);\n            \$strengthBonus = \$this->strengthWeaknessAdjustment(\$comboSubjects, \$strengthSet, \$weaknessSet);\n\n            \$final = (float) min(98, round(\$academicScore * 0.70 + \$interestScore * 0.30, 2));",
    "            \$interestScore = \$this->combinationInterestScore(\$groupLabel, \$interestScores, \$categoryScores, \$totalVotes);\n            \$academicScore = \$this->combinationAcademicScore(\$comboSubjects, \$subjectAvgScores, \$studentSubjectIds);\n            \$strengthBonus = \$this->strengthWeaknessAdjustment(\$comboSubjects, \$strengthSet, \$weaknessSet);\n            \$pathName = match (\$groupLabel) {\n                'Science Combination' => 'Science Path',\n                'Humanities Combination' => 'Humanities Path',\n                'Commercial Combination' => 'Commercial Path',\n                'Agriculture Combination' => 'Agriculture Path',\n                default => null,\n            };\n            \$prefsBoost = \$pathName ? (float) (\$prefsBoosts[\$pathName] ?? 0) : 0;\n            if (\$prefsBoost > 0) {\n                \$interestScore = min(98, \$interestScore * 0.55 + \$prefsBoost * 0.45);\n            }\n            \$final = (float) min(98, round(\$academicScore * 0.50 + \$interestScore * 0.35 + \$strengthBonus * 0.15, 2));",
    $t
);
$t = str_replace(
    "            \$groupLabel = match (\$pathName) {\n                'Science Path'    => 'Science Combination',\n                'Humanities Path' => 'Humanities Combination',\n                default           => 'Humanities Combination',\n            };",
    "            \$groupLabel = match (\$pathName) {\n                'Science Path'     => 'Science Combination',\n                'Humanities Path'  => 'Humanities Combination',\n                'Commercial Path'  => 'Commercial Combination',\n                'Agriculture Path' => 'Agriculture Combination',\n                default            => 'Humanities Combination',\n            };",
    $t
);
$t = str_replace(
    "        \$pathKeywords = [\n            'Science Path'    => ['science', 'physics', 'chemistry', 'biology', 'mathematics', 'math', 'maths',\n                                  'medicine', 'doctor', 'engineer', 'engineering', 'laboratory', 'lab', 'research',\n                                  'technology', 'nursing', 'pharmacist', 'agriculture', 'computer', 'programming'],\n            'Humanities Path' => ['english', 'history', 'geography', 'social', 'law', 'lawyer', 'journalism',\n                                  'writing', 'literature', 'arts', 'teacher', 'education', 'sociology',\n                                  'political', 'media', 'communication', 'language', 'philosophy'],\n        ];",
    "        \$pathKeywords = [\n            'Science Path'     => ['science', 'physics', 'chemistry', 'biology', 'mathematics', 'math', 'maths',\n                                   'medicine', 'doctor', 'engineer', 'engineering', 'laboratory', 'lab', 'research',\n                                   'technology', 'nursing', 'pharmacist', 'computer', 'programming'],\n            'Humanities Path'  => ['english', 'history', 'geography', 'social', 'law', 'lawyer', 'journalism',\n                                   'writing', 'literature', 'arts', 'teacher', 'education', 'sociology',\n                                   'political', 'media', 'communication', 'language', 'philosophy'],\n            'Commercial Path'  => ['accounting', 'business', 'commerce', 'economics', 'finance', 'banking',\n                                   'entrepreneur', 'marketing', 'trade', 'management'],\n            'Agriculture Path' => ['agriculture', 'farming', 'crop', 'livestock', 'agronomy', 'environment',\n                                   'natural resources', 'food security', 'rural'],\n        ];",
    $t
);
file_put_contents($p, $t);
echo "IntelligentAnalysisService ok\n";

$p = $base . '/app/Services/RecommendationEngine.php';
$t = file_get_contents($p);
$t = str_replace("            \$personalBonus = ((\$student->id + \$career->id) % 7) * 0.4;\n\n            \$finalScore = (float) min(98, round(\$baseScore + \$subjectBonus + \$academicBonus + \$personalBonus, 2));", "            \$finalScore = (float) min(98, round(\$baseScore + \$subjectBonus + \$academicBonus, 2));", $t);
$t = str_replace("            \$personalBonus = ((\$student->id + \$combo->id) % 5) * 0.6;\n            \$finalScore = min(98, round(\$baseScore + \$personalBonus, 2));", "            \$finalScore = min(98, round(\$baseScore, 2));", $t);
file_put_contents($p, $t);
echo "RecommendationEngine ok\n";

$p = $base . '/app/Services/ChatbotService.php';
$t = file_get_contents($p);
if (strpos($t, 'Fast local answers') === false) {
    $t = str_replace(
        "    public function respond(string \$userMessage, User \$student, array \$history = []): string\n    {\n        \$systemPrompt = \$this->buildSystemPrompt(\$student);",
        "    public function respond(string \$userMessage, User \$student, array \$history = []): string\n    {\n        if (empty(\$this->geminiKey) && empty(\$this->anthropicKey)) {\n            return \$this->fallback(\$userMessage, \$student);\n        }\n\n        \$systemPrompt = \$this->buildSystemPrompt(\$student);",
        $t
    );
}
$t = str_replace('Http::timeout(5)->post', 'Http::timeout(15)->post', $t);
$t = str_replace('Http::timeout(8)', 'Http::timeout(15)', $t);
file_put_contents($p, $t);
echo "ChatbotService ok\n";

foreach ([
    [$base . '/resources/views/ussd/simulator.blade.php', "body: JSON.stringify({ session_id: sessionId, phone_number: phoneNumber, text }),\n            });", "credentials: 'same-origin',\n                body: JSON.stringify({ session_id: sessionId, phone_number: phoneNumber, text }),\n            });"],
    [$base . '/resources/views/dashboard/student/subject-combinations.blade.php', "body: JSON.stringify({ subject: this.subject, new_score: parseFloat(this.score) }),\n            });", "credentials: 'same-origin',\n                body: JSON.stringify({ subject: this.subject, new_score: parseFloat(this.score) }),\n            });"],
] as $patch) {
    $t = file_get_contents($patch[0]);
    if (strpos($t, "credentials: 'same-origin'") === false) {
        $t = str_replace($patch[1], $patch[2], $t);
        file_put_contents($patch[0], $t);
    }
}
$p = $base . '/resources/views/dashboard/student/subject-combinations.blade.php';
$t = file_get_contents($p);
$t = str_replace("this.error = 'Network error", "this.error = 'Could not reach the server. Run start-backend.bat and refresh.", $t);
file_put_contents($p, $t);
$p = $base . '/resources/views/dashboard/student/chatbot.blade.php';
$t = file_get_contents($p);
$t = str_replace("'Could not reach the assistant. Please refresh and try again.'", "'Could not reach the assistant. Run start-backend.bat and refresh the page.'", $t);
file_put_contents($p, $t);
$roadmap = $base . '/resources/views/dashboard/student/career-roadmap.blade.php';
if (file_exists($roadmap)) unlink($roadmap);
echo "ALL DONE\n";
