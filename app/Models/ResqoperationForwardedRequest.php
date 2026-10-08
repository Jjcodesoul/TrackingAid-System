<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResqoperationForwardedRequest extends Model
{
    protected $table = 'resqoperation_forwarded_requests';

    protected $guarded = [];

    protected $casts = [
        'payload_json' => 'array',
        'forwarded_at' => 'datetime',
    ];
}