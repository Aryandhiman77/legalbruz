<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStatusHistory extends Model
{
    protected $fillable = [
        'case_id',
        'old_status',
        'new_status',
        'changed_by',
        'note',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(TrademarkOppositionCase::class, 'case_id');
    }
}
