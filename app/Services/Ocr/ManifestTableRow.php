<?php

namespace App\Services\Ocr;

class ManifestTableRow
{
    public function __construct(
        public readonly int $rowIndex,
        public readonly ?string $documentoDesde,
        public readonly ?string $documentoHasta,
        public readonly string $pedidoNumber,
        public readonly string $nombreLocal,
        public readonly int $cubetas,
        public readonly int $cajasBoIsa,
        public readonly int $refrigerado,
        public readonly int $controlado,
        public readonly ?string $formaPago,
        public readonly ?string $observaciones,
        public readonly float $confidenceScore,
    ) {}

    public function total(): int
    {
        return $this->cubetas + $this->cajasBoIsa + $this->refrigerado + $this->controlado;
    }
}
