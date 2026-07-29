<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpectedCommitment extends Model
{
    protected $fillable = [
        'workspace_id',
        'subscription_id',
        'plan_version_id',
        'due_date',
        'expected_amount',
        'currency',
        'matching_cost_entry_id',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'expected_amount' => 'decimal:4',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function planVersion(): BelongsTo
    {
        return $this->belongsTo(PlanVersion::class);
    }

    public function matchingCostEntry(): BelongsTo
    {
        return $this->belongsTo(CostEntry::class, 'matching_cost_entry_id');
    }
}
