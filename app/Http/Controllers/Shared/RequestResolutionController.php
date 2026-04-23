<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Services\RequestResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestResolutionController extends Controller
{
    public function __construct(
        protected RequestResolutionService $resolutionService,
    ) {}

    /**
     * List client requests with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ClientRequest::with(['requestedBy:id,name', 'resolvedBy:id,name,role', 'center:id,name']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }
        if ($request->filled('center_id')) {
            $query->where('center_id', $request->center_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // Supervisor only sees requests from their active center
        if ($request->user()->role === 'supervisor' && $request->has('active_center_id')) {
            $query->where('center_id', $request->active_center_id);
        }

        return response()->json(
            $query->orderByDesc('created_at')->paginate(20)
        );
    }

    /**
     * Approve a pending request.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $request->validate(['comment' => 'nullable|string|max:500']);

        $result = $this->resolutionService->resolve(
            $id,
            $request->user()->id,
            'approved',
            $request->input('comment')
        );

        return response()->json($result, $result['success'] ? 200 : 409);
    }

    /**
     * Reject a pending request (comment required).
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate(['comment' => 'required|string|max:500']);

        $result = $this->resolutionService->resolve(
            $id,
            $request->user()->id,
            'rejected',
            $request->input('comment')
        );

        return response()->json($result, $result['success'] ? 200 : 409);
    }
}
