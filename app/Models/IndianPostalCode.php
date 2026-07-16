<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndianPostalCode extends Model
{
    protected $fillable = [
        'pincode',
        'country',
        'state',
        'district',
        'city',
        'country_code',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];
}
