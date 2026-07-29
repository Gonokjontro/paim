<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentAccount extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'friendly_name',
        'type',
        'provider_issuer',
        'masked_identifier',
        'billing_currency',
        'expiry_month',
        'expiry_year',
        'status',
        'spend_limit',
        'notes',
    ];

    protected $casts = [
        'expiry_month' => 'integer',
        'expiry_year' => 'integer',
        'spend_limit' => 'decimal:2',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function costEntries(): HasMany
    {
        return $this->hasMany(CostEntry::class);
    }
}
