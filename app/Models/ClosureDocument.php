<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClosureDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'route_closure_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'ocr_status',
        'ocr_raw_text',
        'ocr_processed_at',
        'uploaded_by',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'file_size'        => 'integer',
            'sort_order'       => 'integer',
            'ocr_processed_at' => 'datetime',
        ];
    }

    public function closure(): BelongsTo
    {
        return $this->belongsTo(RouteClosure::class, 'route_closure_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function manifestItems(): HasMany
    {
        return $this->hasMany(ManifestItem::class, 'closure_document_id');
    }
}
