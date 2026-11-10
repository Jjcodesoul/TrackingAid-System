<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StorageLocation extends Model
{
    protected $fillable = ['name', 'warehouse_name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeMambaling(Builder $query): Builder
    {
        return $query->where('warehouse_name', 'like', '%Mambaling%');
    }

    public function scopeAvailableInMambaling(Builder $query): Builder
    {
        return $query->mambaling()->where('is_active', true);
    }

    public function scopeDesignatedMambaling(Builder $query): Builder
    {
        return $query->where('name', 'Mambaling Storage')
            ->where('warehouse_name', 'Mambaling, Cebu City, Philippines');
    }
}
