<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * List clients, optionally filtered by route.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        return response()->json(
            $query->where('is_active', true)->orderBy('name')->paginate(50)
        );
    }

    /**
     * Create a new client.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'route_id'    => 'required|uuid|exists:routes,id',
            'client_code' => 'required|string|max:50',
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string',
            'phone'       => 'nullable|string|max:20',
        ]);

        $client = Client::create([
            ...$request->only('route_id', 'client_code', 'name', 'address', 'phone'),
            'created_by' => $request->user()->id,
        ]);

        AuditService::log('CREATE_CLIENT', 'clients', $client->id);

        return response()->json($client, 201);
    }

    /**
     * Update an existing client.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name'    => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'phone'   => 'nullable|string|max:20',
        ]);

        $client = Client::findOrFail($id);
        $client->update($request->only('name', 'address', 'phone'));

        AuditService::log('UPDATE_CLIENT', 'clients', $client->id);

        return response()->json($client);
    }
}
