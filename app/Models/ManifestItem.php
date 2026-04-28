<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManifestItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'closure_document_id',
        'route_closure_id',
        'order_id',
        'row_index',
        'documento_desde',
        'documento_hasta',
        'pedido_number',
        'nombre_local',
        'cubetas',
        'cajas_bolsa',
        'refrigerado',
        'controlado',
        'forma_pago',
        'observaciones',
        'confidence_score',
        'is_manually_corrected',
    ];

    protected function casts(): array
    {
        return [
            'row_index'              => 'integer',
            'cubetas'                => 'integer',
            'cajas_bolsa'            => 'integer',
            'refrigerado'            => 'integer',
            'controlado'             => 'integer',
            'confidence_score'       => 'decimal:2',
            'is_manually_corrected'  => 'boolean',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(ClosureDocument::class, 'closure_document_id');
    }

    public function closure(): BelongsTo
    {
        return $this->belongsTo(RouteClosure::class, 'route_closure_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
