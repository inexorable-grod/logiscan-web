<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\ClosureDocument;
use App\Models\ManifestItem;
use App\Models\Order;
use App\Services\Ocr\ManifestParserService;
use App\Services\Ocr\TesseractOcrService;
use App\Services\OrderMatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessManifestOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        private readonly string $closureDocumentId,
    ) {}

    public function handle(
        TesseractOcrService $ocrService,
        ManifestParserService $parserService,
        OrderMatchingService $matchingService,
    ): void {
        $document = ClosureDocument::findOrFail($this->closureDocumentId);
        $closure = $document->closure;

        // Mark as processing
        $document->update(['ocr_status' => 'processing']);
        if ($closure->status === 'pending_documents') {
            $closure->update(['status' => 'pending_ocr']);
        }

        try {
            // Download image to temp file
            $tempPath = $this->downloadToTemp($document);

            // Run OCR
            $rawText = $ocrService->extractText($tempPath);

            // Clean up temp file
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            // Parse manifest structure
            $parsed = $parserService->parse($rawText);

            // Save raw OCR text
            $document->update([
                'ocr_status'       => 'completed',
                'ocr_raw_text'     => $rawText,
                'ocr_processed_at' => now(),
            ]);

            // Get clients for this route for matching
            $routeClients = Client::where('route_id', $closure->route_id)
                ->where('is_active', true)
                ->get();

            // Create manifest items and orders
            foreach ($parsed['rows'] as $row) {
                // Try to match client
                $matchedClient = $matchingService->matchClient($row->nombreLocal, $routeClients);

                // Create or find order
                $order = Order::updateOrCreate(
                    [
                        'route_closure_id' => $closure->id,
                        'pedido_number'    => $row->pedidoNumber,
                    ],
                    [
                        'route_id'            => $closure->route_id,
                        'client_id'           => $matchedClient?->id,
                        'client_name_ocr'     => $row->nombreLocal,
                        'expected_cubetas'    => $row->cubetas,
                        'expected_cajas_bolsa' => $row->cajasBoIsa,
                        'expected_refrigerado' => $row->refrigerado,
                        'expected_controlado'  => $row->controlado,
                        'expected_total'       => $row->total(),
                        'source'              => 'ocr',
                        'is_verified'         => $row->confidenceScore >= config('ocr.confidence_threshold'),
                    ]
                );

                // Create manifest item
                ManifestItem::create([
                    'closure_document_id' => $document->id,
                    'route_closure_id'    => $closure->id,
                    'order_id'            => $order->id,
                    'row_index'           => $row->rowIndex,
                    'documento_desde'     => $row->documentoDesde,
                    'documento_hasta'     => $row->documentoHasta,
                    'pedido_number'       => $row->pedidoNumber,
                    'nombre_local'        => $row->nombreLocal,
                    'cubetas'             => $row->cubetas,
                    'cajas_bolsa'         => $row->cajasBoIsa,
                    'refrigerado'         => $row->refrigerado,
                    'controlado'          => $row->controlado,
                    'confidence_score'    => $row->confidenceScore,
                ]);
            }

            // Link existing scans to orders
            $matchingService->linkScansToOrders(
                $closure->orders()->get(),
                $closure->route_id,
                $closure->operation_date->toDateString()
            );

            // Update closure status
            $closure->update(['status' => 'pending_review']);

            Log::info("OCR completed for closure document {$document->id}: {$parsed['route_number']}, {$parsed['total_docs']} docs, " . count($parsed['rows']) . " rows extracted.");

        } catch (\Throwable $e) {
            $isTesseractMissing = str_contains($e->getMessage(), 'tesseract') || str_contains($e->getMessage(), 'not found');

            $document->update([
                'ocr_status'       => 'failed',
                'ocr_raw_text'     => $isTesseractMissing
                    ? 'Tesseract OCR no está instalado en el servidor. El procesamiento OCR estará disponible cuando se migre a un servidor con Tesseract.'
                    : $e->getMessage(),
                'ocr_processed_at' => now(),
            ]);

            Log::error("OCR failed for closure document {$document->id}: {$e->getMessage()}");

            // Don't retry if Tesseract is not installed — it won't fix itself
            if ($isTesseractMissing) {
                $this->fail($e);
                return;
            }

            throw $e;
        }
    }

    private function downloadToTemp(ClosureDocument $document): string
    {
        $contents = Storage::get($document->file_path);
        $extension = pathinfo($document->file_name, PATHINFO_EXTENSION) ?: 'jpg';
        $tempPath = sys_get_temp_dir() . '/ocr_' . $document->id . '.' . $extension;
        file_put_contents($tempPath, $contents);

        return $tempPath;
    }
}
