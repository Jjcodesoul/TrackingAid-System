<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginEvent extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'succeeded',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'succeeded' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
