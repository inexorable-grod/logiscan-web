<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use App\Models\Scan;
use Illuminate\Support\Collection;

class OrderMatchingService
{
    /**
     * Match an OCR-extracted client name to a Client record on the given route.
     * Uses similar_text() for fuzzy matching.
     */
    public function matchClient(string $ocrName, Collection $routeClients): ?Client
    {
        $bestMatch = null;
        $bestScore = 0;
        $ocrNormalized = $this->normalize($ocrName);

        foreach ($routeClients as $client) {
            $clientNormalized = $this->normalize($client->name);

            similar_text($ocrNormalized, $clientNormalized, $percent);

            if ($percent > $bestScore) {
                $bestScore = $percent;
                $bestMatch = $client;
            }
        }

        // Require at least 60% similarity
        if ($bestScore >= 60) {
            return $bestMatch;
        }

        return null;
    }

    /**
     * Link existing scans to orders based on client_id matching.
     * Scans on the same route with matching client_id and operation date get linked.
     */
    public function linkScansToOrders(Collection $orders, string $routeId, string $operationDate): int
    {
        $linked = 0;

        foreach ($orders as $order) {
            if (!$order->client_id) {
                continue;
            }

            $count = Scan::where('route_id', $routeId)
                ->where('client_id', $order->client_id)
                ->whereNull('order_id')
                ->whereDate('scanned_at', $operationDate)
                ->update(['order_id' => $order->id]);

            $linked += $count;
        }

        return $linked;
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}
