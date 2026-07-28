<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'question_id',
        'option_text',
        'mapped_career_category',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }
}
