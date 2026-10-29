<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    protected $fillable = ['name', 'warehouse_name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
