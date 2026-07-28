<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'title',
        'category',
        'description',
        'required_skills',
    ];

    // ── Relationships ─────────────────────────────────────────────

    /** Subjects linked to this career */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'career_subjects')
                    ->withPivot('importance');
    }

    /** Only the required subjects */
    public function requiredSubjects()
    {
        return $this->belongsToMany(Subject::class, 'career_subjects')
                    ->wherePivot('importance', 'required');
    }

    /** University programs that lead to this career */
    public function universityPrograms()
    {
        return $this->belongsToMany(
            UniversityProgram::class,
            'program_careers',
            'career_id',
            'program_id'
        );
    }

    /** Recommendations made for this career */
    public function recommendations()
    {
        return $this->morphMany(Recommendation::class, 'recommended');
    }
}
