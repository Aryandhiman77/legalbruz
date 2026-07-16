<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrademarkExecutionAction extends Model
{
    protected $fillable = [
        'case_id',
        'action_name',
        'is_required',
        'status',
        'admin_note',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(StuckTrademarkCase::class, 'case_id');
    }
}
