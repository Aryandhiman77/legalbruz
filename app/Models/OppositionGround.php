<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OppositionGround extends Model
{
    protected $fillable = [
        'case_id',
        'ground_name',
        'admin_note',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(TrademarkOppositionCase::class, 'case_id');
    }
}
