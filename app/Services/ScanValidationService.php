<?php

namespace App\Services;

class ScanValidationService
{
    /**
     * Validate a scanned barcode and determine its type.
     *
     * Rules:
     *  - Only numeric characters allowed.
     *  - 5 digits  → type 'rf' (radiofrecuencia)
     *  - 10 digits → type 'toggle' (bulto/cubeta, determined by client toggle)
     *  - 11 digits → type 'modal' (controlado/refrigerado, user selects)
     *  - Any other length → invalid.
     *
     * @param  string  $barcode  The raw barcode string to validate.
     * @return array{valid: bool, type: string|null, error: string|null}
     */
    public function validate(string $barcode): array
    {
        if (!ctype_digit($barcode)) {
            return [
                'valid' => false,
                'type'  => null,
                'error' => 'El código de barras debe contener solo caracteres numéricos.',
            ];
        }

        $length = strlen($barcode);

        return match ($length) {
            5  => ['valid' => true, 'type' => 'rf',     'error' => null],
            10 => ['valid' => true, 'type' => 'toggle', 'error' => null],
            11 => ['valid' => true, 'type' => 'modal',  'error' => null],
            default => [
                'valid' => false,
                'type'  => null,
                'error' => "Código no reconocido ({$length} dígitos). Se esperan 5, 10 u 11 dígitos.",
            ],
        };
    }
}
