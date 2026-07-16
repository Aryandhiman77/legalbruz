<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExaminationReplyRequestedDocument extends Model
{
    protected $fillable = [
        'case_id',
        'stage_request_id',
        'document_name',
        'is_required',
        'is_uploaded_by_client',
        'uploaded_file_path',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_uploaded_by_client' => 'boolean',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(ExaminationReportReplyCase::class, 'case_id');
    }

    public function stageRequest(): BelongsTo
    {
        return $this->belongsTo(ExaminationReplyStageRequest::class, 'stage_request_id');
    }
}
