<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OperationCenter;
use App\Models\Route;
use App\Models\Client;
use App\Models\ClientRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $stats = [];

        if (in_array($user->role, ['ti_admin', 'gerente_ops'])) {
            $stats = [
                'users' => User::where('is_active', true)->count(),
                'centers' => OperationCenter::where('is_active', true)->count(),
                'routes' => Route::where('is_active', true)->count(),
                'clients' => Client::where('is_active', true)->count(),
                'pending_requests' => ClientRequest::where('status', 'pending')->count(),
            ];
        } elseif ($user->role === 'supervisor') {
            $centerId = session('active_center_id');
            if ($centerId) {
                $routeIds = Route::where('center_id', $centerId)->pluck('id');
                $stats = [
                    'routes' => Route::where('center_id', $centerId)->where('is_active', true)->count(),
                    'clients' => Client::whereIn('route_id', $routeIds)->where('is_active', true)->count(),
                    'pending_requests' => ClientRequest::where('center_id', $centerId)->where('status', 'pending')->count(),
                ];
            }
        }

        return view('dashboard', compact('stats'));
    }
}
