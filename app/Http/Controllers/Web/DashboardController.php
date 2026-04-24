<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OperationCenter;
use App\Models\Route;
use App\Models\Client;
use App\Models\ClientRequest;
use App\Models\Scan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $stats = [];
        $scanStats = [];
        $scansPerOperator = collect();

        if (in_array($user->role, ['ti_admin', 'gerente_ops'])) {
            $stats = [
                'users' => User::where('is_active', true)->count(),
                'centers' => OperationCenter::where('is_active', true)->count(),
                'routes' => Route::where('is_active', true)->count(),
                'clients' => Client::where('is_active', true)->count(),
                'pending_requests' => ClientRequest::where('status', 'pending')->count(),
            ];

            $scanStats = [
                'today' => Scan::whereDate('scanned_at', today())->count(),
                'week'  => Scan::where('scanned_at', '>=', now()->startOfWeek())->count(),
                'total' => Scan::count(),
            ];

            $scansPerOperator = Scan::select('user_id', DB::raw('count(*) as total'))
                ->whereDate('scanned_at', today())
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->limit(10)
                ->with('user:id,name')
                ->get();

        } elseif ($user->role === 'supervisor') {
            $centerId = session('active_center_id');
            if ($centerId) {
                $routeIds = Route::where('center_id', $centerId)->pluck('id');
                $stats = [
                    'routes' => Route::where('center_id', $centerId)->where('is_active', true)->count(),
                    'clients' => Client::whereIn('route_id', $routeIds)->where('is_active', true)->count(),
                    'pending_requests' => ClientRequest::where('center_id', $centerId)->where('status', 'pending')->count(),
                ];

                $scanStats = [
                    'today' => Scan::whereIn('route_id', $routeIds)->whereDate('scanned_at', today())->count(),
                    'week'  => Scan::whereIn('route_id', $routeIds)->where('scanned_at', '>=', now()->startOfWeek())->count(),
                    'total' => Scan::whereIn('route_id', $routeIds)->count(),
                ];

                $scansPerOperator = Scan::select('user_id', DB::raw('count(*) as total'))
                    ->whereIn('route_id', $routeIds)
                    ->whereDate('scanned_at', today())
                    ->groupBy('user_id')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->with('user:id,name')
                    ->get();
            }
        }

        return view('dashboard', compact('stats', 'scanStats', 'scansPerOperator'));
    }
}
