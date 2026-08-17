<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerApplication extends Model
{
    protected $fillable = [
        'career_job_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'current_location',
        'years_experience',
        'linkedin_url',
        'portfolio_url',
        'cover_letter',
        'resume_path',
        'resume_original_name',
        'status',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'years_experience' => 'decimal:1',
        'reviewed_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(CareerJob::class, 'career_job_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
