<?php

namespace App\Services;

class BarcodeParserService
{
    /**
     * Parse a barcode into pedido (order) number and package number.
     *
     * Rules:
     * - controlado / refrigerado (10 digits): 6 order + 4 package
     * - cubeta / bulto (10 digits):           7 order + 3 package
     * - RF (11 digits):                       6 order + 5 package
     * - bulto test (11 digits):               8 order + 3 package
     *
     * RF vs bulto-test distinction for 11-digit codes:
     * RF has zero-padded last 5 digits (e.g., 00001), bulto-test has last 3 as sequential.
     *
     * @return array{pedido_number: string|null, package_number: string|null}
     */
    public static function parse(string $barcode, string $scanType): array
    {
        $len = strlen($barcode);

        // Only parse numeric barcodes of 10-11 digits
        if (!ctype_digit($barcode) || $len < 10 || $len > 11) {
            return ['pedido_number' => null, 'package_number' => null];
        }

        if ($len === 10) {
            if (in_array($scanType, ['controlado', 'refrigerado'])) {
                // 6 order + 4 package
                return [
                    'pedido_number'  => substr($barcode, 0, 6),
                    'package_number' => substr($barcode, -4),
                ];
            }

            // cubeta, bulto: 7 order + 3 package
            return [
                'pedido_number'  => substr($barcode, 0, 7),
                'package_number' => substr($barcode, -3),
            ];
        }

        // 11 digits
        if ($scanType === 'rf') {
            // 6 order + 5 package (zero-padded)
            return [
                'pedido_number'  => substr($barcode, 0, 6),
                'package_number' => substr($barcode, -5),
            ];
        }

        // bulto test: 8 order + 3 package
        return [
            'pedido_number'  => substr($barcode, 0, 8),
            'package_number' => substr($barcode, -3),
        ];
    }
}
