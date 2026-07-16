<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OppositionRegistryUpdate extends Model
{
    protected $fillable = [
        'case_id',
        'update_type',
        'update_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'update_date' => 'date',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(TrademarkOppositionCase::class, 'case_id');
    }
}
