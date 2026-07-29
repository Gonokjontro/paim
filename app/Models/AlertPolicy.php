<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertPolicy extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'event_type',
        'scope_type',
        'scope_id',
        'threshold_value',
        'cool_down_hours',
        'is_enabled',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:4',
        'cool_down_hours' => 'integer',
        'is_enabled' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
