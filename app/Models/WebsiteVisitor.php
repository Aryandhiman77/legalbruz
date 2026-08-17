<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebsiteVisitor extends Model
{
    protected $fillable = [
        'visitor_token',
        'first_path',
        'last_path',
        'page_views',
        'first_visited_at',
        'last_visited_at',
        'service_first_visited_at',
        'first_service',
    ];

    protected function casts(): array
    {
        return [
            'first_visited_at' => 'datetime',
            'last_visited_at' => 'datetime',
            'service_first_visited_at' => 'datetime',
        ];
    }

    public function serviceVisits(): HasMany
    {
        return $this->hasMany(WebsiteServiceVisit::class);
    }
}
