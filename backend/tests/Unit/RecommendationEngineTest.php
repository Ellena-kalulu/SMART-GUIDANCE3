<?php

use App\Models\Career;
use App\Services\RecommendationEngine;

it('requires strong academic fit before recommending a career', function () {
    $engine = new RecommendationEngine(new class extends \App\Services\IntelligentAnalysisService {
    });

    $career = new Career([
        'title' => 'Software Engineer',
        'category' => 'Science',
        'description' => 'Builds software systems.',
    ]);

    $career->setRelation('subjects', collect([
        (object) [
            'id' => 1,
            'pivot' => (object) ['importance' => 'required'],
        ],
        (object) [
            'id' => 2,
            'pivot' => (object) ['importance' => 'required'],
        ],
    ]));

    $fit = $engine->calculateCareerFitScore(
        $career,
        ['Science' => 20],
        [1 => 45, 2 => 50],
        ['Science Interest' => 70]
    );

    expect($fit['final_score'])->toBeLessThan(55);
    expect($fit['strong_fit'])->toBeFalse();
});
