<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteServiceVisit extends Model
{
    protected $fillable = [
        'website_visitor_id',
        'service_key',
        'first_path',
        'last_path',
        'page_views',
        'first_visited_at',
        'last_visited_at',
    ];

    protected function casts(): array
    {
        return [
            'first_visited_at' => 'datetime',
            'last_visited_at' => 'datetime',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(WebsiteVisitor::class, 'website_visitor_id');
    }
}
