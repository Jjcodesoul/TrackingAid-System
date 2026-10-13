<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportRequest extends Model
{
    protected $fillable = [
        'report_type',
        'member_id',
        'requested_by',
        'system_name',
        'status',
        'format',
        'filters',
        'notes',
        'file_name',
        'file_path',
    ];

    protected $casts = [
        'filters' => 'array',
    ];
}
