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
        'min_stock',
        'expiration_date',
        'sku',
    ];

    protected $casts = [
        'min_stock' => 'integer',
        'expiration_date' => 'date',
    ];

    public function stockBatches()
    {
        return $this->hasMany(StockBatch::class);
    }

    public function getTotalStockAttribute()
    {
        if ($this->relationLoaded('stockBatches')) {
            return (int) $this->stockBatches->sum('quantity');
        }

        return (int) $this->stockBatches()->sum('quantity');
    }

    public function getNextExpirationDateAttribute()
    {
        $batches = $this->relationLoaded('stockBatches')
            ? $this->stockBatches
            : $this->stockBatches()->get();

        $batchExpiration = $batches
            ->filter(fn (StockBatch $batch): bool => (int) $batch->quantity > 0 && filled($batch->expiration_date))
            ->map(fn (StockBatch $batch) => \Carbon\Carbon::parse($batch->expiration_date))
            ->sort()
            ->first();

        return $batchExpiration ?: $this->expiration_date;
    }

    public function getStockStatusAttribute()
    {
        $stock = $this->total_stock;
        if ($stock <= 0) return 'OUT OF STOCK';
        $threshold = (int) ($this->min_stock ?? 10);
        if ($stock < $threshold) return 'LOW STOCK';
        return 'IN STOCK';
    }
}
