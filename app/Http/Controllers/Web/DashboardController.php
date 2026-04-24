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
        $scansByType = [];
        $scansTrendByOperator = [];
        $clientsPerRoute = [];
        $requestsByStatus = [];

        if (in_array($user->role, ['ti_admin', 'gerente_ops'])) {
            $stats = [
                'users'            => User::where('is_active', true)->count(),
                'centers'          => OperationCenter::where('is_active', true)->count(),
                'routes'           => Route::where('is_active', true)->count(),
                'clients'          => Client::where('is_active', true)->count(),
                'pending_requests' => ClientRequest::where('status', 'pending')->count(),
            ];

            $scanStats = [
                'today' => Scan::whereDate('scanned_at', today())->count(),
                'week'  => Scan::where('scanned_at', '>=', now()->startOfWeek())->count(),
                'total' => Scan::count(),
            ];

            $scansByType = Scan::whereDate('scanned_at', today())
                ->selectRaw('scan_type, count(*) as total')
                ->groupBy('scan_type')
                ->pluck('total', 'scan_type')
                ->toArray();

            $scansTrendByOperator = $this->buildTrendByOperator();

            $clientsPerRoute = Client::select('route_id', DB::raw('count(*) as total'))
                ->where('is_active', true)
                ->groupBy('route_id')
                ->with('route:id,route_number')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            $requestsByStatus = ClientRequest::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

        } elseif ($user->role === 'supervisor') {
            $centerId = session('active_center_id');
            if ($centerId) {
                $routeIds = Route::where('center_id', $centerId)->pluck('id');
                $stats = [
                    'routes'           => Route::where('center_id', $centerId)->where('is_active', true)->count(),
                    'clients'          => Client::whereIn('route_id', $routeIds)->where('is_active', true)->count(),
                    'pending_requests' => ClientRequest::where('center_id', $centerId)->where('status', 'pending')->count(),
                ];

                $scanStats = [
                    'today' => Scan::whereIn('route_id', $routeIds)->whereDate('scanned_at', today())->count(),
                    'week'  => Scan::whereIn('route_id', $routeIds)->where('scanned_at', '>=', now()->startOfWeek())->count(),
                    'total' => Scan::whereIn('route_id', $routeIds)->count(),
                ];

                $scansByType = Scan::whereIn('route_id', $routeIds)
                    ->whereDate('scanned_at', today())
                    ->selectRaw('scan_type, count(*) as total')
                    ->groupBy('scan_type')
                    ->pluck('total', 'scan_type')
                    ->toArray();

                $scansTrendByOperator = $this->buildTrendByOperator($routeIds);

                $clientsPerRoute = Client::select('route_id', DB::raw('count(*) as total'))
                    ->whereIn('route_id', $routeIds)
                    ->where('is_active', true)
                    ->groupBy('route_id')
                    ->with('route:id,route_number')
                    ->orderByDesc('total')
                    ->limit(10)
                    ->get();

                $requestsByStatus = ClientRequest::where('center_id', $centerId)
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();
            }
        }

        return view('dashboard', compact(
            'stats', 'scanStats', 'scansByType',
            'scansTrendByOperator', 'clientsPerRoute', 'requestsByStatus'
        ));
    }

    /**
     * Build the 7-day trend data grouped by operator.
     * Returns: [ { date, label, operators: { 'Name': count, ... } }, ... ]
     */
    private function buildTrendByOperator($routeIds = null): array
    {
        $dates = collect(range(6, 0))->map(fn($d) => now()->subDays($d)->toDateString());

        $query = Scan::select('user_id', DB::raw('DATE(scanned_at) as scan_date'), DB::raw('count(*) as total'))
            ->whereDate('scanned_at', '>=', $dates->first())
            ->groupBy('user_id', 'scan_date')
            ->with('user:id,name');

        if ($routeIds) {
            $query->whereIn('route_id', $routeIds);
        }

        $raw = $query->get();

        // Collect unique operator names
        $operators = $raw->pluck('user.name')->unique()->filter()->values()->toArray();

        // Build per-day structure
        $trend = $dates->map(function ($date) use ($raw) {
            $dayData = $raw->where('scan_date', $date);
            $ops = [];
            foreach ($dayData as $row) {
                $name = $row->user->name ?? 'Desconocido';
                $ops[$name] = $row->total;
            }
            $d = \Carbon\Carbon::parse($date);
            return [
                'date'      => $date,
                'label'     => $d->locale('es')->shortDayName,
                'dayNum'    => $d->day,
                'operators' => $ops,
            ];
        })->values()->toArray();

        return [
            'operators' => $operators,
            'days'      => $trend,
        ];
    }
}
