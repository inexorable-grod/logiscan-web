<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'request_type',
        'status',
        'requested_by',
        'center_id',
        'route_id',
        'request_data',
        'admin_comment',
        'resolved_by',
        'resolved_at',
        'resolved_by_role',
    ];

    protected function casts(): array
    {
        return [
            'request_data' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(OperationCenter::class, 'center_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
