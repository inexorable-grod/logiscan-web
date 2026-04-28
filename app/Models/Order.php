<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasUuids;

    protected $fillable = [
        'route_id',
        'client_id',
        'route_closure_id',
        'pedido_number',
        'client_name_ocr',
        'expected_cubetas',
        'expected_cajas_bolsa',
        'expected_refrigerado',
        'expected_controlado',
        'expected_total',
        'source',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'expected_cubetas'     => 'integer',
            'expected_cajas_bolsa' => 'integer',
            'expected_refrigerado' => 'integer',
            'expected_controlado'  => 'integer',
            'expected_total'       => 'integer',
            'is_verified'          => 'boolean',
        ];
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function closure(): BelongsTo
    {
        return $this->belongsTo(RouteClosure::class, 'route_closure_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(Scan::class);
    }

    public function manifestItems(): HasMany
    {
        return $this->hasMany(ManifestItem::class);
    }
}
