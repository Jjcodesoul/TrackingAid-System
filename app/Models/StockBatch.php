<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockBatch extends Model
{
    protected $fillable = [
        'item_id',
        'quantity',
        'supplier',
        'date_received',
        'expiration_date',
        'created_by_user_id',
        'movement_type',
        'storage_location',
    ];

    protected $casts = [
        'date_received' => 'date',
        'expiration_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
