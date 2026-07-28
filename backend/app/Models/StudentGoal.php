<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGoal extends Model
{
    protected $fillable = [
        'student_id',
        'career_id',
        'university_program_id',
        'status',
        'last_match_score',
        'previous_match_score',
        'progress_snapshot',
        'tracking_message',
        'last_analyzed_at',
    ];

    protected $casts = [
        'last_match_score'     => 'decimal:2',
        'previous_match_score' => 'decimal:2',
        'progress_snapshot'    => 'array',
        'last_analyzed_at'     => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    public function universityProgram(): BelongsTo
    {
        return $this->belongsTo(UniversityProgram::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
