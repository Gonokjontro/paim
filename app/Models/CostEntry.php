<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostEntry extends Model
{
    protected $fillable = [
        'workspace_id',
        'subscription_id',
        'payment_account_id',
        'entry_type',
        'posted_date',
        'original_amount',
        'original_currency',
        'base_amount',
        'fx_rate',
        'status',
        'reference_number',
        'description',
        'reversal_of_entry_id',
    ];

    protected $casts = [
        'posted_date' => 'date',
        'original_amount' => 'decimal:4',
        'base_amount' => 'decimal:4',
        'fx_rate' => 'decimal:6',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(CostEntry::class, 'reversal_of_entry_id');
    }
}
