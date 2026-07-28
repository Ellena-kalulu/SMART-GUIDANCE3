<?php
/**
 * One-off patch script for demo fixes — run: php scripts/apply-demo-fixes.php
 */
$base = dirname(__DIR__);

function patch(string $path, string $old, string $new): void {
    $full = $base . '/' . ltrim($path, '/');
    $content = file_get_contents($full);
    if (!str_contains($content, $old)) {
        echo "SKIP (already patched or pattern missing): {$path}\n";
        return;
    }
    file_put_contents($full, str_replace($old, $new, $content));
    echo "OK: {$path}\n";
}

// 1. Subject combinations — pass $profile, remove heavy getFullReport call
patch('app/Http/Controllers/StudentController.php',
    "        \$combinationReport = null;\n        try {\n            \$combinationReport = \$this->academicProgress->getFullReport(\$user, \$analysis);\n        } catch (\\Throwable \$e) {\n            Log::error('combinationReport failed', ['error' => \$e->getMessage()]);\n        }\n\n        \$simulationSubjects",
    "        \$simulationSubjects");

patch('app/Http/Controllers/StudentController.php',
    "        return view('dashboard.student.subject-combinations', compact(\n            'user', 'analysis', 'recommendationHistory', 'combinations',",
    "        return view('dashboard.student.subject-combinations', compact(\n            'user', 'profile', 'analysis', 'recommendationHistory', 'combinations',");

echo "Done.\n";
