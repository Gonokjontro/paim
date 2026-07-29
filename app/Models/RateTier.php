<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateTier extends Model
{
    protected $fillable = [
        'subscription_id',
        'meter_unit_id',
        'tier_min_units',
        'tier_max_units',
        'unit_price',
        'currency',
        'effective_date',
    ];

    protected $casts = [
        'tier_min_units' => 'decimal:4',
        'tier_max_units' => 'decimal:4',
        'unit_price' => 'decimal:6',
        'effective_date' => 'date',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function meterUnit(): BelongsTo
    {
        return $this->belongsTo(MeterUnit::class);
    }
}
