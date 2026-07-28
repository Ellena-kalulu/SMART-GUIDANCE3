<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'meta',
        'ip_address',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Static helper ─────────────────────────────────────────────

    /**
     * Quick log entry from anywhere in the app.
     *
     * Usage:
     *   ActivityLog::record('submit_assessment', $attempt);
     *   ActivityLog::record('login');
     */
    public static function record(
        string $action,
        ?Model $model = null,
        array  $meta  = []
    ): self {
        return self::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model'      => $model ? get_class($model) : null,
            'model_id'   => $model?->getKey(),
            'meta'       => $meta ?: null,
            'ip_address' => request()->ip(),
        ]);
    }

    /** Scope: logs for a specific action */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
