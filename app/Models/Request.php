<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'request_code',
        'inventory_id',
        'source',
        'quantity',
        'priority',
        'status',
        'purpose',
        'responder_email',
        'notification_status',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function borrowReleases()
    {
        return $this->hasMany(BorrowRelease::class);
    }
}
