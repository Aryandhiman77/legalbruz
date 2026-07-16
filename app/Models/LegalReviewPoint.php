<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalReviewPoint extends Model
{
    protected $fillable = [
        'case_id',
        'review_point',
        'admin_note',
        'is_client_visible',
    ];

    protected $casts = [
        'is_client_visible' => 'boolean',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(TrademarkOppositionCase::class, 'case_id');
    }
}
