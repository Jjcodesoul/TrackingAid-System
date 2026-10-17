<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnItem extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'inventory_id',
        'borrow_release_id',
        'processed_by_user_id',
        'idempotency_key',
        'quantity',
        'condition',
        'notes'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function borrowRelease()
    {
        return $this->belongsTo(BorrowRelease::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
