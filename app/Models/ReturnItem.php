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
        'returned_by_name',
        'received_by_user_id',
        'idempotency_key',
        'quantity',
        'condition',
        'storage_location',
        'returned_at',
        'notes'
    ];

    protected $casts = ['returned_at' => 'datetime'];

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

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
