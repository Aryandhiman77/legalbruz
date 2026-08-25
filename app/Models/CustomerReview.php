<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CustomerReview extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_title',
        'logo_path',
        'review',
        'rating',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function homepageReviews(): Collection
    {
        if (! Schema::hasTable((new static)->getTable())) {
            return collect();
        }

        return static::published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function getInitialsAttribute(): string
    {
        return collect(preg_split('/\s+/', trim($this->customer_name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }
}
