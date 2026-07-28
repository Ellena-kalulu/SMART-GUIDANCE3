<?php
$p = 'c:/laragon/www/SMART-GUIDANCE-TOOL-main/backend/app/Http/Controllers/StudentController.php';
$t = file_get_contents($p);

// Remove careerRoadmap
$t = preg_replace('/\s*\/\*\* Career Roadmap Planner[\s\S]*?return view\(\'dashboard\.student\.career-roadmap\'[\s\S]*?\);\s*\}\s*\n\}/', "\n}", $t);

// Remove combinationReport block
$t = preg_replace('/\s*\$combinationReport = null;\s*try \{\s*\$combinationReport = \$this->academicProgress->getFullReport\(\$user, \$analysis\);\s*\} catch \(\\Throwable \$e\) \{\s*Log::error\(\'combinationReport failed\', \[\s*\'error\' => \$e->getMessage\(\),\s*\]\);\s*\}\s*/', "\n        ", $t);
$t = str_replace("'form12Subjects', 'mscePaths', 'targetForm', 'combinationReport',", "'form12Subjects', 'mscePaths', 'targetForm',", $t);

// saveSubjectPreferences re-analyze
if (strpos($t, 'Preference re-analysis failed') === false) {
    $t = str_replace(
        "        return redirect(\$redirectUrl)\n            ->with('success', \$action === 'analyze'",
        "        if (\$action === 'analyze') {\n            \$attempt = AssessmentAttempt::where('student_id', \$user->id)\n                ->where('status', 'completed')\n                ->latest('completed_at')\n                ->first();\n            if (\$attempt) {\n                try {\n                    \$this->intelligence->analyzeAndStore(\$attempt, \$user);\n                    \$this->recommendationEngine->generate(\$attempt->fresh());\n                } catch (\\Throwable \$e) {\n                    Log::error('Preference re-analysis failed', ['error' => \$e->getMessage()]);\n                }\n            }\n        }\n\n        return redirect(\$redirectUrl)\n            ->with('success', \$action === 'analyze'",
        $t
    );
}

// dashboard performance
$t = str_replace(
    "        \$this->refreshAnalysisIfNeeded(\$user, \$latestAttempt);\n        \$this->ensureRecommendationsGenerated(\$user, \$latestAttempt);\n\n        \$assessmentsCompleted",
    "        \$assessmentsCompleted",
    $t
);

file_put_contents($p, $t);
echo "fixed StudentController\n";
echo (strpos(file_get_contents($p), 'careerRoadmap') !== false ? "WARNING: careerRoadmap still present\n" : "careerRoadmap removed\n");
echo (strpos(file_get_contents($p), 'combinationReport') !== false ? "WARNING: combinationReport still present\n" : "combinationReport removed\n");
