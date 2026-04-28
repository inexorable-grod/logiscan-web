<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasUuids;

    protected $fillable = [
        'center_id',
        'route_number',
        'description',
        'is_active',
        'created_by',
        'current_closure_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(OperationCenter::class, 'center_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closures(): HasMany
    {
        return $this->hasMany(RouteClosure::class);
    }

    public function currentClosure(): BelongsTo
    {
        return $this->belongsTo(RouteClosure::class, 'current_closure_id');
    }
}
