<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RouteClosure;
use App\Services\MachComparisonService;
use Illuminate\Http\JsonResponse;

class MachComparisonController extends Controller
{
    public function __construct(
        protected MachComparisonService $comparisonService,
    ) {}

    /**
     * Get MACH comparison for a route closure.
     */
    public function show(string $closureId): JsonResponse
    {
        $closure = RouteClosure::findOrFail($closureId);

        $comparison = $this->comparisonService->compare($closure);

        return response()->json($comparison);
    }
}
