<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentResponse extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'option_id',
        'text_response',
        'scale_value',
    ];

    protected $casts = [
        'scale_value' => 'integer',
    ];

    public function attempt()
    {
        return $this->belongsTo(AssessmentAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }

    public function option()
    {
        return $this->belongsTo(QuestionOption::class, 'option_id');
    }
}
