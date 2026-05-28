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
        'sku'
    ];

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    // TOTAL STOCK
    public function getTotalStockAttribute()
    {
        return $this->stockBatches->sum('quantity');
    }

    // STOCK STATUS
    public function getStockStatusAttribute()
    {
        $total = $this->total_stock;

        if ($total <= 0) return 'OUT OF STOCK';
        if ($total < 10) return 'LOW STOCK';
        return 'IN STOCK';
    }
}