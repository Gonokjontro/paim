<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostComponent extends Model
{
    protected $fillable = [
        'plan_version_id',
        'name',
        'type',
        'amount',
        'currency',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
    ];

    public function planVersion(): BelongsTo
    {
        return $this->belongsTo(PlanVersion::class);
    }
}
