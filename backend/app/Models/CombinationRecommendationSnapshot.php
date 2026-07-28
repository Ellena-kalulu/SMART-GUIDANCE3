<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CombinationRecommendationSnapshot extends Model
{
    protected $fillable = [
        'student_id',
        'term',
        'form_level',
        'recommended_combination',
        'score',
        'all_scores',
    ];

    protected $casts = [
        'score'      => 'decimal:2',
        'all_scores' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}