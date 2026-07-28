<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounsellingSession extends Model
{
    protected $fillable = [
        'counsellor_id',
        'student_id',
        'scheduled_at',
        'venue',
        'message',
        'status',
        'initiated_by',
        'appointment_type',
        'student_reason',
        'counsellor_notes',
    ];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function counsellor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counsellor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isForAll(): bool
    {
        return $this->student_id === null;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_confirmation';
    }

    public function appointmentTypeLabel(): string
    {
        return match($this->appointment_type) {
            'in_person' => 'In Person',
            'online'    => 'Online (Video Call)',
            'phone'     => 'Phone Call',
            default     => 'In Person',
        };
    }
}
