<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'student_number',
        'form_level',
        'stream',
        'disability_type',
        'date_of_birth',
        'gender',
        'interests',
        'skills',
        'preferred_career',
        'subject_preferences',
    ];

    protected $casts = [
        'date_of_birth'        => 'date',
        'subject_preferences'  => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────────

    /** The user this profile belongs to */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Accessors ─────────────────────────────────────────────────

    /** Full display name pulled from the related user */
    public function getFullNameAttribute(): string
    {
        return $this->user->name ?? '';
    }

    /** Age calculated from date_of_birth */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth
            ? $this->date_of_birth->age
            : null;
    }
}
