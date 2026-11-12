<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResqoperationForwardedRequest extends Model
{
    protected $table = 'resqperation_forwarded_requests';

    protected $guarded = [];

    protected $casts = [
        'payload_json' => 'array',
        'forwarded_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function borrowReleases()
    {
        return $this->hasMany(BorrowRelease::class, 'forwarded_request_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }
}