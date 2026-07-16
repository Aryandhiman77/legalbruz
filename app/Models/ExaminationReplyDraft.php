<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExaminationReplyDraft extends Model
{
    protected $fillable = [
        'case_id',
        'draft_file_path',
        'original_name',
        'version',
        'status',
        'client_comment',
        'uploaded_by',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(ExaminationReportReplyCase::class, 'case_id');
    }
}
