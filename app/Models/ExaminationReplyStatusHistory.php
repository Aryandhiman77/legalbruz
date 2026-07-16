<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExaminationReplyStatusHistory extends Model
{
    protected $fillable = [
        'case_id',
        'old_admin_status',
        'new_admin_status',
        'old_client_stage',
        'new_client_stage',
        'note',
        'changed_by',
        'actor_id',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(ExaminationReportReplyCase::class, 'case_id');
    }
}
