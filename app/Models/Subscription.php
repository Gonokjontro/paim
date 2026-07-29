<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'tool_id',
        'payment_account_id',
        'owner_user_id',
        'name',
        'type',
        'status',
        'start_date',
        'end_date',
        'cancellation_deadline',
        'access_end_date',
        'auto_renew',
        'billing_cadence_months',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cancellation_deadline' => 'date',
        'access_end_date' => 'date',
        'auto_renew' => 'boolean',
        'billing_cadence_months' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function planVersions(): HasMany
    {
        return $this->hasMany(PlanVersion::class);
    }

    public function currentPlanVersion(): HasOne
    {
        return $this->hasOne(PlanVersion::class)->latestOfMany();
    }

    public function costEntries(): HasMany
    {
        return $this->hasMany(CostEntry::class);
    }

    public function expectedCommitments(): HasMany
    {
        return $this->hasMany(ExpectedCommitment::class);
    }

    public function tokenPackages(): HasMany
    {
        return $this->hasMany(TokenPackage::class);
    }

    public function usageEntries(): HasMany
    {
        return $this->hasMany(UsageEntry::class);
    }
}
