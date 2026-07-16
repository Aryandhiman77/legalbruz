<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrademarkExecutionUpdate extends Model
{
    protected $fillable = [
        'case_id',
        'title',
        'note',
        'stage',
        'file_path',
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
