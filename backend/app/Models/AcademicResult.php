<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AcademicResult extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'student_id',
        'subject_id',
        'term',
        'form_level',
        'score',
        'grade',
        'teacher_remarks',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public static function gradeFromScore(float $score): string
    {
        return match(true) {
            $score >= 80 => 'A',
            $score >= 70 => 'B',
            $score >= 60 => 'C',
            $score >= 50 => 'D',
            $score >= 40 => 'E',
            default      => 'F',
        };
    }

    public function scopeForTerm($query, string $term)
    {
        return $query->where('term', $term);
    }

    public function scopeForForm($query, string $form)
    {
        return $query->where('form_level', $form);
    }

    public static function latestPerSubject(User $student): Collection
    {
        $formRank = ['Form 1' => 1, 'Form 2' => 2, 'Form 3' => 3, 'Form 4' => 4];
        $termRank = ['Term 1' => 1, 'Term 2' => 2, 'Term 3' => 3];

        return $student->academicResults()
            ->with('subject')
            ->get()
            ->groupBy('subject_id')
            ->map(function ($rows) use ($formRank, $termRank) {
                return $rows->sortByDesc(fn (self $r) =>
                    ($formRank[$r->form_level] ?? 0) * 1000
                    + ($termRank[$r->term] ?? 0) * 100
                    + ($r->updated_at?->timestamp ?? 0)
                )->first();
            })
            ->filter()
            ->values();
    }
}