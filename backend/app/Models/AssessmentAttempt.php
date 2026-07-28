<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentAttempt extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function responses()
    {
        return $this->hasMany(AssessmentResponse::class, 'attempt_id');
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class, 'attempt_id');
    }

    public function analysis()
    {
        return $this->hasOne(AssessmentAnalysis::class, 'attempt_id');
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function tallyCareerCategories(): array
    {
        return $this->responses()
            ->with('option')
            ->get()
            ->filter(fn ($r) => $r->option?->mapped_career_category)
            ->groupBy(fn ($r) => $r->option->mapped_career_category)
            ->map(fn ($group) => $group->count())
            ->sortDesc()
            ->toArray();
    }
}
