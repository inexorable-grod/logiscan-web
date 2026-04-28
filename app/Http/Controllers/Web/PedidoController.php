<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Route;
use App\Models\RouteClosure;
use App\Models\Scan;
use App\Services\MachComparisonService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PedidoController extends Controller
{
    public function __construct(
        protected MachComparisonService $comparisonService,
    ) {}

    public function index(Request $request): View
    {
        $routes = Route::where('is_active', true)->orderBy('route_number')->get();
        $selectedRouteId = $request->query('route_id');
        $selectedDate = $request->query('date', now()->toDateString());

        $comparison = null;
        $closure = null;

        if ($selectedRouteId) {
            $closure = RouteClosure::where('route_id', $selectedRouteId)
                ->where('operation_date', $selectedDate)
                ->first();

            if ($closure) {
                $comparison = $this->comparisonService->compare($closure);
            }
        }

        return view('pedidos', compact('routes', 'selectedRouteId', 'selectedDate', 'comparison', 'closure'));
    }
}
