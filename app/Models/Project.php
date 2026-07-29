<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'code',
        'client_name',
        'budget',
        'color',
        'is_tax_deductible',
        'description',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'is_tax_deductible' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ProjectAllocation::class);
    }
}
