<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'question_text',
        'type',
        'category',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id')
                    ->orderBy('order');
    }

    public function responses()
    {
        return $this->hasMany(AssessmentResponse::class, 'question_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
