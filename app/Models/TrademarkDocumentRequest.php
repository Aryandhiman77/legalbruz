<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrademarkDocumentRequest extends Model
{
    protected $fillable = [
        'case_id',
        'document_name',
        'reason',
        'message',
        'status',
        'uploaded_file',
        'admin_note',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(StuckTrademarkCase::class, 'case_id');
    }
}
