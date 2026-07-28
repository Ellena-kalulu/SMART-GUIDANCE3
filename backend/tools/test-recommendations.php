<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\AssessmentAttempt;
use App\Models\Recommendation;
use App\Services\RecommendationEngine;
$attempt = AssessmentAttempt::where('status', 'completed')->latest('completed_at')->first();
if (!$attempt) { echo "No completed attempt\n"; exit(1); }
echo "Attempt ID: {$attempt->id}, student: {$attempt->student_id}\n";
echo "Category scores: " . json_encode($attempt->tallyCareerCategories()) . "\n";
try {
    app(RecommendationEngine::class)->generate($attempt->fresh());
    echo "OK - recommendations: " . Recommendation::where('student_id', $attempt->student_id)->count() . "\n";
} catch (Throwable $e) {
    echo "ERR: {$e->getMessage()}\n{$e->getFile()}:{$e->getLine()}\n";
}
