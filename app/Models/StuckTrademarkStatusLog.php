<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StuckTrademarkStatusLog extends Model
{
    protected $fillable = [
        'case_id',
        'from_status',
        'to_status',
        'actor_type',
        'actor_id',
        'title',
        'message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(StuckTrademarkCase::class, 'case_id');
    }
}
