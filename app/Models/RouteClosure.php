<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RouteClosure extends Model
{
    use HasUuids;

    protected $fillable = [
        'route_id',
        'closed_by',
        'approved_by',
        'operation_date',
        'status',
        'notes',
        'closed_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'operation_date' => 'date',
            'closed_at'      => 'datetime',
            'approved_at'    => 'datetime',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ClosureDocument::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function manifestItems(): HasMany
    {
        return $this->hasMany(ManifestItem::class);
    }
}
