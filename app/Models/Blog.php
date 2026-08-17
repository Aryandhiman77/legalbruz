<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'tags',
        'author_name',
        'featured_image_path',
        'featured_image_alt',
        'status',
        'published_at',
        'is_featured',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image_path',
        'schema_markup',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Blog $blog) {
            if (! $blog->slug) {
                $blog->slug = static::uniqueSlug($blog->title);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getIsPublicAttribute(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags(Str::markdown($this->content ?? '')));

        return max(1, (int) ceil($wordCount / 220));
    }

    public function getMetaTitleAttribute(): string
    {
        return $this->seo_title ?: $this->title;
    }

    public function getMetaDescriptionAttribute(): string
    {
        return $this->seo_description ?: Str::limit(strip_tags($this->excerpt ?: $this->content), 160, '');
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'article';
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
