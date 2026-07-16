<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftVersion extends Model
{
    protected $fillable = [
        'application_id',
        'version_no',
        'classes',
        'goods_services',
        'mark_preview_path',
        'tm_a_draft_path',
        'prepared_by',
        'status',
        'client_decision',
        'client_comments',
        'published_at',
        'approved_at',
    ];

    protected $casts = [
        'classes' => 'array',
        'published_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
