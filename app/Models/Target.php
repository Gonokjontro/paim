<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Target extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'scope_type',
        'scope_id',
        'period_type',
        'target_amount',
        'currency',
        'basis',
        'warning_threshold_pct',
        'critical_threshold_pct',
        'status',
    ];

    protected $casts = [
        'target_amount' => 'decimal:4',
        'warning_threshold_pct' => 'integer',
        'critical_threshold_pct' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
