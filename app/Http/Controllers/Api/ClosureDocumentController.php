<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessManifestOcr;
use App\Models\ClosureDocument;
use App\Models\RouteClosure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClosureDocumentController extends Controller
{
    /**
     * Upload a manifest document to a route closure.
     */
    public function store(Request $request, string $closureId): JsonResponse
    {
        $request->validate([
            'file'       => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $closure = RouteClosure::findOrFail($closureId);

        $file = $request->file('file');
        $path = $file->store("closures/{$closureId}", 'public');

        $document = ClosureDocument::create([
            'route_closure_id' => $closure->id,
            'file_path'        => $path,
            'file_name'        => $file->getClientOriginalName(),
            'mime_type'        => $file->getMimeType(),
            'file_size'        => $file->getSize(),
            'ocr_status'       => 'pending',
            'uploaded_by'      => auth()->id(),
            'sort_order'       => $request->input('sort_order', 0),
        ]);

        // Update closure status if it was waiting for documents
        if ($closure->status === 'pending_documents') {
            $closure->update(['status' => 'pending_ocr']);
        }

        // Dispatch OCR job
        ProcessManifestOcr::dispatch($document->id);

        return response()->json($document, 201);
    }

    /**
     * Delete a closure document.
     */
    public function destroy(string $closureId, string $documentId): JsonResponse
    {
        $document = ClosureDocument::where('route_closure_id', $closureId)
            ->findOrFail($documentId);

        // Delete file from storage
        Storage::disk('public')->delete($document->file_path);

        // Delete related manifest items
        $document->manifestItems()->delete();

        $document->delete();

        return response()->json(null, 204);
    }

    /**
     * Re-run OCR processing on a document.
     */
    public function reprocess(string $closureId, string $documentId): JsonResponse
    {
        $document = ClosureDocument::where('route_closure_id', $closureId)
            ->findOrFail($documentId);

        // Clear existing manifest items for this document
        $document->manifestItems()->delete();

        // Reset OCR status
        $document->update([
            'ocr_status'   => 'pending',
            'ocr_raw_text' => null,
        ]);

        // Re-dispatch OCR job
        ProcessManifestOcr::dispatch($document->id);

        return response()->json(['message' => 'OCR reprocesamiento en cola.'], 202);
    }
}
