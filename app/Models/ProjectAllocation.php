<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'project_id',
        'subscription_id',
        'allocation_percentage',
        'allocated_amount',
        'notes',
    ];

    protected $casts = [
        'allocation_percentage' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
