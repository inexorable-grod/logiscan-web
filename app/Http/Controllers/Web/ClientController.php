<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Route;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('route');

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        $clients = $query->orderBy('name')->paginate(20);
        $routes = Route::where('is_active', true)->orderBy('route_number')->get();

        return view('clients.index', compact('clients', 'routes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'route_id' => 'required|exists:routes,id',
            'client_code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
        ]);

        $client = Client::create([
            'name' => $request->name,
            'route_id' => $request->route_id,
            'client_code' => $request->client_code,
            'address' => $request->address,
            'phone' => $request->phone,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        AuditService::log('CLIENT_CREATED', 'clients', $client->id, null, auth()->id());

        return redirect()->back()->with('success', 'Cliente creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'route_id' => 'required|exists:routes,id',
            'client_code' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
        ]);

        $client = Client::findOrFail($id);
        $oldValues = $client->only(['name', 'route_id', 'client_code', 'address', 'phone']);

        $client->update([
            'name' => $request->name,
            'route_id' => $request->route_id,
            'client_code' => $request->client_code,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);

        AuditService::log('CLIENT_UPDATED', 'clients', $client->id, $oldValues, auth()->id());

        return redirect()->back()->with('success', 'Cliente actualizado correctamente.');
    }
}
