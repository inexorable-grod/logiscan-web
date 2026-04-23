<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class OperationCenter extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'code',
        'address',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CenterUser::class, 'center_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class, 'center_id');
    }

    public function clientRequests(): HasMany
    {
        return $this->hasMany(ClientRequest::class, 'center_id');
    }
}
