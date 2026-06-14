<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category',
        'unit_type',
        'size_weight',
        'target_beneficiary',
        'variant',
        'type',
        'storage_location',
        'expiration_date',
        'sku',
    ];

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function getTotalStockAttribute()
    {
        return $this->stockBatches->sum('quantity');
    }

    public function getStockStatusAttribute()
    {
        $stock = $this->total_stock;
        if ($stock <= 0) return 'OUT OF STOCK';
        if ($stock < 10) return 'LOW STOCK';
        return 'IN STOCK';
    }
}
