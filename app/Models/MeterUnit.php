<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeterUnit extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'symbol',
        'description',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
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
