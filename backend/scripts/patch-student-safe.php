<?php
$p = 'c:/laragon/www/SMART-GUIDANCE-TOOL-main/backend/app/Http/Controllers/StudentController.php';
$t = file_get_contents($p);
$orig = $t;

// 1. Dashboard: skip heavy refresh
$t = str_replace(
    "        \$this->refreshAnalysisIfNeeded(\$user, \$latestAttempt);\n        \$this->ensureRecommendationsGenerated(\$user, \$latestAttempt);\n\n        \$assessmentsCompleted = AssessmentAttempt::where('student_id', \$user->id)",
    "        \$assessmentsCompleted = AssessmentAttempt::where('student_id', \$user->id)",
    $t
);

// 2. Remove combinationReport block
$t = str_replace(
    "        \$combinationReport = null;\n        try {\n            \$combinationReport = \$this->academicProgress->getFullReport(\$user, \$analysis);\n        } catch (\\Throwable \$e) {\n            Log::error('combinationReport failed', ['error' => \$e->getMessage()]);\n        }\n\n        // Only show subjects that match MSCE_PATHS",
    "        // Only show subjects that match MSCE_PATHS",
    $t
);
$t = str_replace(
    "            'form12Subjects', 'mscePaths', 'targetForm', 'combinationReport',",
    "            'form12Subjects', 'mscePaths', 'targetForm',",
    $t
);

// 3. saveSubjectPreferences re-analyze
if (strpos($t, 'Preference re-analysis failed') === false) {
    $t = str_replace(
        "        ActivityLog::record('subject_preferences_saved', null, ['student_id' => \$user->id]);\n\n        \$action = \$request->input('action', 'save');",
        "        ActivityLog::record('subject_preferences_saved', null, ['student_id' => \$user->id]);\n\n        \$action = \$request->input('action', 'save');\n\n        if (\$action === 'analyze') {\n            \$attempt = AssessmentAttempt::where('student_id', \$user->id)\n                ->where('status', 'completed')\n                ->latest('completed_at')\n                ->first();\n            if (\$attempt) {\n                try {\n                    \$this->intelligence->analyzeAndStore(\$attempt, \$user);\n                    \$this->recommendationEngine->generate(\$attempt->fresh());\n                } catch (\\Throwable \$e) {\n                    Log::error('Preference re-analysis failed', ['error' => \$e->getMessage()]);\n                }\n            }\n        }",
        $t
    );
}

// 4. Add voiceAsk before saveRecommendation
if (strpos($t, 'function voiceAsk') === false) {
    $insert = <<<'PHP'

    /** Voice mode: ask the AI assistant without requiring session_id */
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
    $t = str_replace('    /** Save a recommendation */', $insert . '    /** Save a recommendation */', $t);
}

// 5. Remove careerRoadmap method only (between its docblock and closing brace before final class brace)
$start = strpos($t, '    /** Career Roadmap Planner');
if ($start !== false) {
    $end = strpos($t, '        return view(\'dashboard.student.career-roadmap\'', $start);
    if ($end !== false) {
        $end = strpos($t, '    }', $end);
        if ($end !== false) {
            $t = substr($t, 0, $start) . substr($t, $end + 4);
        }
    }
}

if ($t === $orig) {
    echo "no changes made\n";
} else {
    file_put_contents($p, $t);
    echo "StudentController patched\n";
}
echo 'bytes: ' . strlen($t) . "\n";
echo (strpos($t, 'function voiceAsk') !== false ? "voiceAsk ok\n" : "voiceAsk MISSING\n");
echo (strpos($t, 'careerRoadmap') !== false ? "careerRoadmap STILL THERE\n" : "careerRoadmap removed\n");
