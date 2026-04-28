<?php

namespace App\Services\Ocr;

class ManifestParserService
{
    /**
     * Parse raw OCR text from a dispatch manifest into structured rows.
     *
     * @return array{route_number: string|null, total_docs: int|null, rows: ManifestTableRow[]}
     */
    public function parse(string $rawText): array
    {
        $lines = array_map('trim', explode("\n", $rawText));
        $lines = array_filter($lines, fn($l) => $l !== '');

        $routeNumber = $this->extractRouteNumber($lines);
        $totalDocs = $this->extractTotalDocs($lines);
        $rows = $this->extractRows($lines);

        return [
            'route_number' => $routeNumber,
            'total_docs'   => $totalDocs,
            'rows'         => $rows,
        ];
    }

    private function extractRouteNumber(array $lines): ?string
    {
        foreach ($lines as $line) {
            if (preg_match('/Ruta[:\s]*(\d+)/i', $line, $m)) {
                return $m[1];
            }
        }
        return null;
    }

    private function extractTotalDocs(array $lines): ?int
    {
        foreach ($lines as $line) {
            if (preg_match('/Total\s+Doctos[:\s]*(\d+)/i', $line, $m)) {
                return (int) $m[1];
            }
        }
        return null;
    }

    /**
     * Extract data rows from the manifest text.
     *
     * The manifest has a known structure with Sub-Total lines separating each order.
     * We look for lines containing a pedido number (7+ digit number) followed by a
     * client name, then parse the numeric columns.
     *
     * @return ManifestTableRow[]
     */
    private function extractRows(array $lines): array
    {
        $rows = [];
        $rowIndex = 0;

        // Find the data region: after the header line containing "Pedido"
        $dataStart = 0;
        foreach ($lines as $i => $line) {
            if (preg_match('/Pedido/i', $line) && preg_match('/Nombre/i', $line)) {
                $dataStart = $i + 1;
                break;
            }
        }

        $currentDocDesde = null;
        $currentDocHasta = null;

        for ($i = $dataStart; $i < count($lines); $i++) {
            $line = $lines[$i];

            // Skip Sub-Total, TOTAL GRAL, and empty lines
            if (preg_match('/^(Sub-Total|TOTAL\s*GRAL|Impresion|Fecha|Hora|Folio)/i', $line)) {
                continue;
            }

            // Try to parse a document range line (e.g., "6.490.078  6.490.078")
            if (preg_match('/^([\d.]+)\s+([\d.]+)\s*$/', $line, $docMatch)) {
                $currentDocDesde = $docMatch[1];
                $currentDocHasta = $docMatch[2];
                continue;
            }

            // Try to parse an order row: pedido number + client name + numeric columns
            // Pattern: pedido_number  CLIENT NAME  numbers...
            if (preg_match('/^(\d{5,10})\s+(.+?)(?:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+))?\s*$/i', $line, $m)) {
                $confidence = 1.0;

                $pedido = $m[1];
                $clientName = trim($m[2]);
                $cubetas = isset($m[3]) ? (int) $m[3] : 0;
                $cajasBoIsa = isset($m[4]) ? (int) $m[4] : 0;
                $refrigerado = isset($m[5]) ? (int) $m[5] : 0;
                $controlado = isset($m[6]) ? (int) $m[6] : 0;

                // Lower confidence if we couldn't parse numeric fields
                if (!isset($m[3])) {
                    $confidence *= 0.5;
                    // Try to extract numbers from next line or remaining text
                    $numericValues = $this->tryExtractNumerics($lines, $i);
                    if ($numericValues) {
                        [$cubetas, $cajasBoIsa, $refrigerado, $controlado] = $numericValues;
                        $confidence *= 1.5; // Bump back up partially
                    }
                }

                // Lower confidence for short or suspicious client names
                if (strlen($clientName) < 3) {
                    $confidence *= 0.4;
                }

                $rows[] = new ManifestTableRow(
                    rowIndex: $rowIndex++,
                    documentoDesde: $currentDocDesde,
                    documentoHasta: $currentDocHasta,
                    pedidoNumber: $pedido,
                    nombreLocal: $clientName,
                    cubetas: $cubetas,
                    cajasBoIsa: $cajasBoIsa,
                    refrigerado: $refrigerado,
                    controlado: $controlado,
                    formaPago: null,
                    observaciones: null,
                    confidenceScore: min(1.0, $confidence),
                );

                $currentDocDesde = null;
                $currentDocHasta = null;
                continue;
            }

            // Alternative pattern: line may have the pedido and name mixed differently
            if (preg_match('/(\d{7})\s+(.+)/i', $line, $altMatch)) {
                $rows[] = new ManifestTableRow(
                    rowIndex: $rowIndex++,
                    documentoDesde: $currentDocDesde,
                    documentoHasta: $currentDocHasta,
                    pedidoNumber: $altMatch[1],
                    nombreLocal: trim($altMatch[2]),
                    cubetas: 0,
                    cajasBoIsa: 0,
                    refrigerado: 0,
                    controlado: 0,
                    formaPago: null,
                    observaciones: null,
                    confidenceScore: 0.3,
                );

                $currentDocDesde = null;
                $currentDocHasta = null;
            }
        }

        return $rows;
    }

    /**
     * Try to extract numeric column values from nearby lines.
     *
     * @return int[]|null [cubetas, cajas_bolsa, refrigerado, controlado]
     */
    private function tryExtractNumerics(array $lines, int $currentIndex): ?array
    {
        // Check if the next line has just numbers
        if (isset($lines[$currentIndex + 1])) {
            $next = trim($lines[$currentIndex + 1]);
            if (preg_match('/^(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $next, $m)) {
                return [(int) $m[1], (int) $m[2], (int) $m[3], (int) $m[4]];
            }
        }
        return null;
    }
}
