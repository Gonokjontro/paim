<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatch extends Model
{
    protected $fillable = [
        'workspace_id',
        'file_name',
        'total_rows',
        'imported_rows',
        'failed_rows',
        'status',
        'error_summary',
    ];

    protected $casts = [
        'total_rows' => 'integer',
        'imported_rows' => 'integer',
        'failed_rows' => 'integer',
        'error_summary' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
