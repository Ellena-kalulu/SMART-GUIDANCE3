<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'code',
        'category',
        'description',
        'is_compulsory',
    ];

    protected $casts = [
        'is_compulsory' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────

    /** Subject combinations that include this subject */
    public function combinations()
    {
        return $this->belongsToMany(
            SubjectCombination::class,
            'combination_subjects',
            'subject_id',
            'combination_id'
        );
    }

    /** Careers that require or recommend this subject */
    public function careers()
    {
        return $this->belongsToMany(Career::class, 'career_subjects')
                    ->withPivot('importance')
                    ->withTimestamps();
    }

    /** University programs that list this subject as a requirement */
    public function universityPrograms()
    {
        return $this->belongsToMany(
            UniversityProgram::class,
            'program_subjects'
        )->withPivot('requirement')->withTimestamps();
    }

    /** Academic results recorded for this subject */
    public function academicResults()
    {
        return $this->hasMany(AcademicResult::class);
    }
}
