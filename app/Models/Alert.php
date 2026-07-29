<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'workspace_id',
        'alert_policy_id',
        'severity',
        'title',
        'message',
        'status',
        'snoozed_until',
        'acknowledged_at',
        'acknowledged_by_user_id',
    ];

    protected $casts = [
        'snoozed_until' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function alertPolicy(): BelongsTo
    {
        return $this->belongsTo(AlertPolicy::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_user_id');
    }
}
