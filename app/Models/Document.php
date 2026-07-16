<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'application_id',
        'user_id',
        'document_type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'status',
        'verification_notes',
        'verified_at',
        'created_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
