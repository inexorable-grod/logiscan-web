<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RouteClosure;
use App\Services\MachComparisonService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouteClosureWebController extends Controller
{
    public function __construct(
        protected MachComparisonService $comparisonService,
    ) {}

    public function index(): View
    {
        $closures = RouteClosure::with(['route:id,route_number,description', 'closedBy:id,name'])
            ->orderByDesc('operation_date')
            ->paginate(20);

        return view('cierres.index', compact('closures'));
    }

    public function show(string $id): View
    {
        $closure = RouteClosure::with([
            'route:id,route_number,description',
            'closedBy:id,name',
            'approvedBy:id,name',
            'documents',
            'manifestItems.order',
            'orders.client:id,name,client_code',
        ])->findOrFail($id);

        $comparison = $this->comparisonService->compare($closure);

        return view('cierres.show', compact('closure', 'comparison'));
    }
}
