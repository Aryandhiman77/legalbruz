<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationTask extends Model
{
    protected $fillable = [
        'application_id',
        'task_code',
        'task_group',
        'title',
        'assignee_type',
        'status',
        'due_at',
        'completed_at',
        'payload',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'payload' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
