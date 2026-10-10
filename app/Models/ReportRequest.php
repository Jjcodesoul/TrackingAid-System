<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRequest extends Model
{
    protected $fillable = [
        'report_type',
        'requested_by',
        'system_name',
        'status',
        'filters',
        'notes',
    ];

    protected $casts = [
        'filters' => 'array',
    ];
}
