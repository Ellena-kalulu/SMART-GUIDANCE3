<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentAnalysis extends Model
{
    protected $fillable = [
        'attempt_id',
        'student_id',
        'interest_scores',
        'strengths',
        'weaknesses',
        'predicted_performance',
        'subject_suitability_scores',
        'top_combination',
        'confidence_score',
        'confidence_level',
        'explanation',
        'subject_conflicts',
    ];

    protected $casts = [
        'interest_scores'           => 'array',
        'strengths'                 => 'array',
        'weaknesses'                => 'array',
        'predicted_performance'     => 'array',
        'subject_suitability_scores'=> 'array',
        'top_combination'           => 'array',
        'confidence_score'          => 'decimal:2',
        'subject_conflicts'         => 'array',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'attempt_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function confidenceLabel(): string
    {
        return match ($this->confidence_level) {
            'very_high' => 'Very High',
            'high'      => 'High',
            'moderate'  => 'Moderate',
            'low'       => 'Low',
            default     => 'Moderate',
        };
    }
}
