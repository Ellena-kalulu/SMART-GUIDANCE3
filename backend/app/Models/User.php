<?php

namespace App\Models;

use App\Notifications\{ResetPasswordNotification, VerifyEmailNotification};
use App\Traits\HasUuid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasUuid;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'alt_phone',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ── Custom notifications (use branded email templates) ────────

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    // ── Role helpers ──────────────────────────────────────────────

    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isStudent(): bool    { return $this->role === 'student'; }
    public function isTeacher(): bool    { return $this->role === 'teacher'; }
    public function isCounsellor(): bool { return $this->role === 'counsellor'; }
    public function isParent(): bool     { return $this->role === 'parent'; }

    // ── Relationships ─────────────────────────────────────────────

    /** Student's extended profile */
    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    /** Academic results (student) */
    public function academicResults()
    {
        return $this->hasMany(AcademicResult::class, 'student_id');
    }

    /** Assessment attempts (student) */
    public function assessmentAttempts()
    {
        return $this->hasMany(AssessmentAttempt::class, 'student_id');
    }

    public function assessmentAnalyses()
    {
        return $this->hasMany(AssessmentAnalysis::class, 'student_id');
    }

    /** Recommendations received (student) */
    public function recommendations()
    {
        return $this->hasMany(Recommendation::class, 'student_id');
    }

    /** Children linked to this parent */
    public function children()
    {
        return $this->belongsToMany(
            User::class,
            'parent_student',
            'parent_id',
            'student_id'
        )->withPivot('relationship')->withTimestamps();
    }

    /** Parents linked to this student */
    public function parents()
    {
        return $this->belongsToMany(
            User::class,
            'parent_student',
            'student_id',
            'parent_id'
        )->withPivot('relationship')->withTimestamps();
    }

    /** Activity logs */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /** Student's chosen career/university goal */
    public function careerGoal()
    {
        return $this->hasOne(StudentGoal::class, 'student_id');
    }
}
