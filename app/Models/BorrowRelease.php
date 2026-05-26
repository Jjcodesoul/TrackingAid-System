<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BorrowRelease extends Model
{
    protected $fillable = [
        'request_id',
        'inventory_id',
        'unit',
        'quantity',
        'purpose',
        'location',
        'released_at'
    ];

    protected $casts = [
        'released_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
