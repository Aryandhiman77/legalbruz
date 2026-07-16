<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OppositionDocument extends Model
{
    protected $fillable = [
        'case_id',
        'document_type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
        'is_required',
        'review_status',
        'review_note',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(TrademarkOppositionCase::class, 'case_id');
    }
}
