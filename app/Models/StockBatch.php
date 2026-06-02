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
        'expiration_date'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}