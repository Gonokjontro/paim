<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UsageEntry extends Model
{
    protected $fillable = [
        'workspace_id',
        'subscription_id',
        'meter_unit_id',
        'model_name',
        'environment_project',
        'usage_date',
        'unit_count',
        'calculated_cost',
        'currency',
        'provider_reference',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'unit_count' => 'decimal:4',
        'calculated_cost' => 'decimal:4',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function meterUnit(): BelongsTo
    {
        return $this->belongsTo(MeterUnit::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(UsageAllocation::class);
    }
}
