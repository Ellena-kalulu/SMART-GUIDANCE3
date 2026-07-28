<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\AssessmentAttempt;
use App\Models\Recommendation;
use App\Services\RecommendationEngine;
$engine = app(RecommendationEngine::class);
foreach (AssessmentAttempt::where('status','completed')->get() as $attempt) {
    $engine->generate($attempt->fresh());
}
echo 'Total recommendations: ' . Recommendation::count() . PHP_EOL;
