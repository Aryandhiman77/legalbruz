<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExaminationReplyDocument extends Model
{
    protected $fillable = [
        'case_id',
        'stage_request_id',
        'document_type',
        'document_title',
        'file_path',
        'original_name',
        'file_type',
        'file_size',
        'visibility',
        'uploaded_by',
        'review_status',
        'review_note',
        'document_note',
        'remarks',
        'stage_key',
        'is_draft',
        'metadata',
    ];

    protected $casts = [
        'is_draft' => 'boolean',
        'metadata' => 'array',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(ExaminationReportReplyCase::class, 'case_id');
    }
}
