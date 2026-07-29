<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanVersion extends Model
{
    protected $fillable = [
        'subscription_id',
        'effective_start_date',
        'effective_end_date',
        'billing_currency',
        'recurring_amount',
        'normalized_monthly_amount',
        'tax_rate',
        'discount_amount',
        'notes',
    ];

    protected $casts = [
        'effective_start_date' => 'date',
        'effective_end_date' => 'date',
        'recurring_amount' => 'decimal:4',
        'normalized_monthly_amount' => 'decimal:4',
        'tax_rate' => 'decimal:2',
        'discount_amount' => 'decimal:4',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function costComponents(): HasMany
    {
        return $this->hasMany(CostComponent::class);
    }
}
