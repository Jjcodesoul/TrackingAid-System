<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected $table = 'inventory';

    protected $fillable = [
        'name',
        'category',
        'sku',
        'type',
        'quantity',
        'expiration',
        'storage_location'
    ];

    protected $casts = [
        'expiration' => 'date',
    ];

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function borrowReleases()
    {
        return $this->hasMany(BorrowRelease::class);
    }

    public function returns()
    {
        return $this->hasMany(ReturnItem::class);
    }
}
