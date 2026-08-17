<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CareerJob extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'location',
        'employment_type',
        'workplace_type',
        'experience_level',
        'salary_range',
        'summary',
        'description',
        'responsibilities',
        'requirements',
        'benefits',
        'application_deadline',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (CareerJob $job) {
            if (! $job->slug) {
                $job->slug = static::uniqueSlug($job->title);
            }
        });
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CareerApplication::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('application_deadline')
                    ->orWhereDate('application_deadline', '>=', today());
            });
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->is_active
            && (! $this->application_deadline || $this->application_deadline->isToday() || $this->application_deadline->isFuture());
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'job';
        $slug = $base;
        $counter = 2;

        while (static::query()
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
