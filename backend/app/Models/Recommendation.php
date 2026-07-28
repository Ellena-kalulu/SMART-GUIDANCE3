<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'attempt_id',
        'type',
        'recommended_id',
        'recommended_type',
        'confidence_score',
        'reason',
        'status',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function attempt()
    {
        return $this->belongsTo(AssessmentAttempt::class, 'attempt_id');
    }

    /**
     * Polymorphic: resolves to Career, SubjectCombination,
     * or UniversityProgram depending on recommended_type.
     */
    public function recommended()
    {
        return $this->morphTo();
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByConfidence($query)
    {
        return $query->orderByDesc('confidence_score');
    }

    // ── Helpers ───────────────────────────────────────────────────

    /** Mark this recommendation as viewed */
    public function markViewed(): void
    {
        if ($this->status === 'pending') {
            $this->update(['status' => 'viewed']);
        }
    }

    /** Accept this recommendation */
    public function accept(): void
    {
        $this->update(['status' => 'accepted']);
    }

    /** Dismiss this recommendation */
    public function dismiss(): void
    {
        $this->update(['status' => 'dismissed']);
    }
}
