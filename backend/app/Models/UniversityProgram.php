<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniversityProgram extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'university',
        'faculty',
        'minimum_points',
        'entry_requirements',
        'description',
    ];

    protected $casts = [
        'minimum_points' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────

    /** Careers this program prepares students for */
    public function careers()
    {
        return $this->belongsToMany(Career::class, 'program_careers', 'program_id', 'career_id');
    }

    /** All entry subjects (required + preferred) with their minimum MSCE grade */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'program_subjects', 'program_id', 'subject_id')
                    ->withPivot('requirement', 'minimum_grade');
    }

    /** Only compulsory entry subjects with their minimum MSCE grade */
    public function requiredSubjects()
    {
        return $this->belongsToMany(Subject::class, 'program_subjects', 'program_id', 'subject_id')
                    ->withPivot('requirement', 'minimum_grade')
                    ->wherePivot('requirement', 'required');
    }

    /** Recommendations made for this program */
    public function recommendations()
    {
        return $this->morphMany(Recommendation::class, 'recommended');
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Check if a student (User) is eligible based on their
     * academic results and required subjects.
     */
    public function isStudentEligible(User $student): bool
    {
        return $this->calculateEligibility($student)['eligible'];
    }

    /**
     * Full eligibility analysis with graduated match score (0–98).
     */
    public function calculateEligibility(User $student): array
    {
        $this->loadMissing('requiredSubjects');

        $avgScores = $student->academicResults()
            ->selectRaw('subject_id, AVG(score) as avg_score')
            ->groupBy('subject_id')
            ->pluck('avg_score', 'subject_id');

        $required = $this->requiredSubjects;
        $subjectDetails = [];
        $metCount = 0;

        foreach ($required as $subject) {
            $avg = (float) ($avgScores->get($subject->id, 0));
            $minGrade = (int) ($subject->pivot->minimum_grade ?? 6);
            $studentPoints = self::scoreToMscePoints($avg);
            $met = $avgScores->has($subject->id) && $studentPoints <= $minGrade;

            if ($met) {
                $metCount++;
            }

            $subjectDetails[] = [
                'subject'        => $subject->name,
                'score'          => round($avg, 1),
                'required_grade' => $minGrade,
                'student_points' => $studentPoints,
                'met'            => $met,
                'has_data'       => $avgScores->has($subject->id),
            ];
        }

        $coverage = $required->isNotEmpty()
            ? ($metCount / $required->count()) * 100
            : 100;

        $performanceScores = collect($subjectDetails)
            ->filter(fn ($s) => $s['has_data'])
            ->pluck('score');

        $performance = $performanceScores->isNotEmpty() ? $performanceScores->avg() : 0;

        $totalPoints = collect($subjectDetails)
            ->filter(fn ($s) => $s['has_data'])
            ->sum('student_points');

        $pointsMet = $this->minimum_points
            ? $totalPoints <= $this->minimum_points
            : true;

        $eligible = $required->isEmpty()
            ? ($performanceScores->isNotEmpty() && $performance >= 50)
            : ($metCount === $required->count() && $pointsMet);

        $matchScore = round(min(98, ($coverage * 0.50) + ($performance * 0.35) + ($eligible ? 13 : 0)), 1);

        return [
            'eligible'     => $eligible,
            'match_score'  => $matchScore,
            'coverage'     => round($coverage, 1),
            'performance'  => round($performance, 1),
            'points_met'   => $pointsMet,
            'total_points' => $totalPoints,
            'subjects'     => $subjectDetails,
        ];
    }

    /** Convert percentage score to MSCE points (1=best, 8=worst). */
    public static function scoreToMscePoints(float $score): int
    {
        return match (true) {
            $score >= 85 => 1,
            $score >= 75 => 2,
            $score >= 65 => 3,
            $score >= 55 => 4,
            $score >= 45 => 5,
            $score >= 35 => 6,
            $score >= 25 => 7,
            default      => 8,
        };
    }
}
