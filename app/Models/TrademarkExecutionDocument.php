<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrademarkExecutionDocument extends Model
{
    protected $fillable = [
        'case_id',
        'document_title',
        'document_type',
        'execution_stage',
        'file_path',
        'admin_note',
        'visible_to_client',
    ];

    protected $casts = [
        'visible_to_client' => 'boolean',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(StuckTrademarkCase::class, 'case_id');
    }
}
