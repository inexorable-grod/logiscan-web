<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceToken extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'is_active',
        'last_used',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
