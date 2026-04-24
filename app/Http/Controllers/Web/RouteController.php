<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OperationCenter;
use App\Models\Route;
use App\Services\AuditService;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::with('center')->orderBy('route_number')->paginate(20);
        $centers = OperationCenter::where('is_active', true)->orderBy('name')->get();
        return view('admin.routes.index', compact('routes', 'centers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'center_id' => 'required|exists:operation_centers,id',
            'route_number' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        $route = Route::create([
            'center_id' => $request->center_id,
            'route_number' => $request->route_number,
            'description' => $request->description,
            'is_active' => true,
        ]);

        AuditService::log('ROUTE_CREATED', 'routes', $route->id, null, auth()->id());

        return redirect()->back()->with('success', 'Ruta creada correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'center_id' => 'required|exists:operation_centers,id',
            'route_number' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        $route = Route::findOrFail($id);
        $oldValues = $route->only(['center_id', 'route_number', 'description']);

        $route->update([
            'center_id' => $request->center_id,
            'route_number' => $request->route_number,
            'description' => $request->description,
        ]);

        AuditService::log('ROUTE_UPDATED', 'routes', $route->id, $oldValues, auth()->id());

        return redirect()->back()->with('success', 'Ruta actualizada correctamente.');
    }

    public function destroy(string $id)
    {
        $route = Route::findOrFail($id);

        if ($route->clients()->exists()) {
            return redirect()->back()->with('error', 'No se puede eliminar la ruta porque tiene clientes asignados.');
        }

        $route->delete();
        AuditService::log('ROUTE_DELETED', 'routes', $id, null, auth()->id());

        return redirect()->back()->with('success', 'Ruta eliminada correctamente.');
    }
}
