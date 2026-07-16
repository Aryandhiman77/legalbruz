<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExaminationReplyStageDraft extends Model
{
    protected $fillable = [
        'case_id',
        'stage_key',
        'admin_status',
        'payload',
        'created_by',
        'saved_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'saved_at' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(ExaminationReportReplyCase::class, 'case_id');
    }
}
