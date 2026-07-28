<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectCombination extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'description',
    ];

    // ── Relationships ─────────────────────────────────────────────

    /** Subjects that make up this combination */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'combination_subjects', 'combination_id', 'subject_id');
    }

    /** Recommendations that suggested this combination */
    public function recommendations()
    {
        return $this->morphMany(Recommendation::class, 'recommended');
    }
}
