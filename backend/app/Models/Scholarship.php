<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    protected $fillable = [
        'name', 'provider', 'description', 'amount',
        'eligibility_path', 'min_grade', 'eligible_careers',
        'application_url', 'deadline', 'type', 'is_active',
    ];

    protected $casts = [
        'min_grade' => 'float',
        'is_active' => 'boolean',
    ];

    public function getEligibleCareersArrayAttribute(): array
    {
        return $this->eligible_careers
            ? array_map('trim', explode(',', $this->eligible_careers))
            : [];
    }
}
