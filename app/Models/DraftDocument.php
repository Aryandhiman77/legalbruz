<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftDocument extends Model
{
    protected $fillable = [
        'case_id',
        'draft_type',
        'file_path',
        'file_name',
        'version',
        'uploaded_by',
        'client_status',
        'client_comment',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(TrademarkOppositionCase::class, 'case_id');
    }
}
