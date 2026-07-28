<?php
$base = dirname(__DIR__);
$ctrlPath = $base . '/app/Http/Controllers/StudentController.php';
$ctrl = file_get_contents($ctrlPath);
if (!str_contains($ctrl, 'combinationReport failed')) {
    $ctrl = str_replace(
        '$combinationReport = $this->academicProgress->getFullReport($user, $analysis);',
        '$combinationReport = null;
        try {
            $combinationReport = $this->academicProgress->getFullReport($user, $analysis);
        } catch (\Throwable $e) {
            Log::error(\'combinationReport failed\', [\'error\' => $e->getMessage()]);
        }',
        $ctrl,
        $c
    );
    if ($c) { file_put_contents($ctrlPath, $ctrl); echo "subjectCombinations patched\n"; } else { echo "subjectCombinations pattern missing\n"; }
} else { echo "subjectCombinations already patched\n"; }
