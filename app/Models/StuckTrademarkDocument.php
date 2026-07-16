<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StuckTrademarkDocument extends Model
{
    protected $fillable = [
        'case_id',
        'user_id',
        'document_type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'status',
        'verification_notes',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(StuckTrademarkCase::class, 'case_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
