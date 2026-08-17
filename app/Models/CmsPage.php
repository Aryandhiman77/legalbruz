<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CmsPage extends Model
{
    public const TERMS = 'terms';
    public const PRIVACY = 'privacy';
    public const REFUND = 'refund';
    public const DISCLAIMER = 'disclaimer';

    private const CACHE_PREFIX = 'cms_page.';

    protected $fillable = [
        'key',
        'title',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function legalKeys(): array
    {
        return [self::TERMS, self::PRIVACY, self::REFUND, self::DISCLAIMER];
    }

    public static function defaults(): array
    {
        return config('cms_pages', []);
    }

    public static function findByKey(string $key): array
    {
        $default = self::defaults()[$key] ?? [
            'title' => Str::headline($key),
            'content' => '',
        ];

        if (! self::tableIsAvailable()) {
            return self::normalizePage($key, $default);
        }

        try {
            return Cache::rememberForever(self::CACHE_PREFIX.$key, function () use ($key, $default): array {
                $page = self::query()
                    ->where('key', $key)
                    ->where('is_active', true)
                    ->first();

                if (! $page) {
                    return self::normalizePage($key, $default);
                }

                return [
                    'key' => $page->key,
                    'title' => $page->title,
                    'content' => $page->content,
                ];
            });
        } catch (\Throwable) {
            return self::normalizePage($key, $default);
        }
    }

    public static function legalPages(): Collection
    {
        self::ensureDefaults();

        if (! self::tableIsAvailable()) {
            return new Collection(collect(self::legalKeys())->map(fn (string $key) => (object) self::findByKey($key))->all());
        }

        return self::query()
            ->whereIn('key', self::legalKeys())
            ->orderByRaw("FIELD(`key`, 'terms', 'privacy', 'refund', 'disclaimer')")
            ->get();
    }

    public static function ensureDefaults(): void
    {
        if (! self::ensureTable()) {
            return;
        }

        foreach (self::defaults() as $key => $page) {
            self::query()->firstOrCreate(
                ['key' => $key],
                [
                    'title' => $page['title'],
                    'content' => $page['content'],
                    'is_active' => true,
                ],
            );
        }
    }

    public static function flushPageCache(?string $key = null): void
    {
        try {
            if ($key) {
                Cache::forget(self::CACHE_PREFIX.$key);
                return;
            }

            foreach (self::legalKeys() as $legalKey) {
                Cache::forget(self::CACHE_PREFIX.$legalKey);
            }
        } catch (\Throwable) {
            //
        }
    }

    protected static function booted(): void
    {
        static::saved(function (self $page): void {
            self::flushPageCache($page->key);
        });
        static::deleted(function (self $page): void {
            self::flushPageCache($page->key);
        });
    }

    private static function normalizePage(string $key, array $page): array
    {
        return [
            'key' => $key,
            'title' => $page['title'] ?? Str::headline($key),
            'content' => $page['content'] ?? '',
        ];
    }

    private static function tableIsAvailable(): bool
    {
        try {
            return Schema::hasTable('cms_pages');
        } catch (\Throwable) {
            return false;
        }
    }

    private static function ensureTable(): bool
    {
        if (self::tableIsAvailable()) {
            return true;
        }

        try {
            Schema::create('cms_pages', function (Blueprint $table): void {
                $table->id();
                $table->string('key')->unique();
                $table->string('title');
                $table->longText('content');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            return true;
        } catch (\Throwable) {
            return self::tableIsAvailable();
        }
    }
}
