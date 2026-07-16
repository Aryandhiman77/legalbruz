<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExaminationReplyStageRequest extends Model
{
    protected $fillable = [
        'case_id',
        'from_stage',
        'to_stage',
        'client_message',
        'internal_note',
        'created_by',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(ExaminationReportReplyCase::class, 'case_id');
    }
}
