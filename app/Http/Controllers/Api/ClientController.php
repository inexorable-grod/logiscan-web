<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * List active clients for a given route.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['route_id' => 'required|uuid|exists:routes,id']);

        $clients = Client::where('route_id', $request->route_id)
            ->where('is_active', true)
            ->select('id', 'route_id', 'client_code', 'name', 'address', 'phone')
            ->orderBy('name')
            ->get();

        return response()->json($clients);
    }
}
