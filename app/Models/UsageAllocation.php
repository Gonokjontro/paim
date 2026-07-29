<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageAllocation extends Model
{
    protected $fillable = [
        'usage_entry_id',
        'token_package_id',
        'allocated_units',
        'effective_cost',
    ];

    protected $casts = [
        'allocated_units' => 'decimal:4',
        'effective_cost' => 'decimal:4',
    ];

    public function usageEntry(): BelongsTo
    {
        return $this->belongsTo(UsageEntry::class);
    }

    public function tokenPackage(): BelongsTo
    {
        return $this->belongsTo(TokenPackage::class);
    }
}
