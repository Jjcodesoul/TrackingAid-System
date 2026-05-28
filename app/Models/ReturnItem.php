<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'inventory_id',
        'quantity',
        'condition',
        'notes'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
