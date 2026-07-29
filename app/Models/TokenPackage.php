<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TokenPackage extends Model
{
    protected $fillable = [
        'workspace_id',
        'subscription_id',
        'meter_unit_id',
        'package_name',
        'purchase_cost',
        'currency',
        'granted_units',
        'consumed_units',
        'remaining_units',
        'purchase_date',
        'expiry_date',
        'allow_carryover',
        'status',
    ];

    protected $casts = [
        'purchase_cost' => 'decimal:4',
        'granted_units' => 'decimal:4',
        'consumed_units' => 'decimal:4',
        'remaining_units' => 'decimal:4',
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'allow_carryover' => 'boolean',
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
