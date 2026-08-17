<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrademarkResult extends Model
{
    protected $fillable = [
        'search_keyword',
        'application_id',
        'application_date',
        'trademark_name',
        'proprietor',
        'status',
        'sub_status',
        'class',
        'type',
        'attorney',
        'state',
        'country',
        'filing_mode',
        'branch_office',
        'ip_office',
        'used_since',
        'valid_upto',
        'description',
        'image_url',
        'detail_url',
        'source_url',
        'validation_errors',
    ];

    protected $casts = [
        'validation_errors' => 'array',
    ];
}