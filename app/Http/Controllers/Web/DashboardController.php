<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OperationCenter;
use App\Models\Route;
use App\Models\Client;
use App\Models\ClientRequest;
use App\Models\RouteClosure;
use App\Models\Scan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $stats = [];
        $scansPerCenter = collect();
        $scansByType = [];
        $scansTrendByOperator = [];
        $clientsPerRoute = [];
        $requestsByStatus = [];
        $recentClosures = collect();

        if (in_array($user->role, ['ti_admin', 'gerente_ops'])) {
            $stats = [
                'users'            => User::where('is_active', true)->count(),
                'centers'          => OperationCenter::where('is_active', true)->count(),
                'routes'           => Route::where('is_active', true)->count(),
                'clients'          => Client::where('is_active', true)->count(),
                'pending_requests' => ClientRequest::where('status', 'pending')->count(),
            ];

            // Scans per center (today, week, total)
            $scansPerCenter = $this->buildScansPerCenter();

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

            $recentClosures = RouteClosure::with(['route:id,route_number'])
                ->where('operation_date', '>=', now()->subDays(7))
                ->orderByDesc('operation_date')
                ->limit(10)
                ->get();

        } elseif ($user->role === 'supervisor') {
            $centerId = session('active_center_id');
            if ($centerId) {
                $routeIds = Route::where('center_id', $centerId)->pluck('id');
                $stats = [
                    'routes'           => Route::where('center_id', $centerId)->where('is_active', true)->count(),
                    'clients'          => Client::whereIn('route_id', $routeIds)->where('is_active', true)->count(),
                    'pending_requests' => ClientRequest::where('center_id', $centerId)->where('status', 'pending')->count(),
                ];

                // Single center for supervisor
                $scansPerCenter = $this->buildScansPerCenter($centerId);

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

                $recentClosures = RouteClosure::with(['route:id,route_number'])
                    ->whereIn('route_id', $routeIds)
                    ->where('operation_date', '>=', now()->subDays(7))
                    ->orderByDesc('operation_date')
                    ->limit(10)
                    ->get();
            }
        }

        return view('dashboard', compact(
            'stats', 'scansPerCenter', 'scansByType',
            'scansTrendByOperator', 'clientsPerRoute', 'requestsByStatus',
            'recentClosures'
        ));
    }

    /**
     * Build scans per distribution center (today, week, total).
     */
    private function buildScansPerCenter(?string $centerId = null): \Illuminate\Support\Collection
    {
        $query = OperationCenter::where('is_active', true);
        if ($centerId) {
            $query->where('id', $centerId);
        }
        $centers = $query->select('id', 'name')->get();

        return $centers->map(function ($center) {
            $routeIds = Route::where('center_id', $center->id)->pluck('id');
            return (object) [
                'name'  => $center->name,
                'today' => Scan::whereIn('route_id', $routeIds)->whereDate('scanned_at', today())->count(),
                'week'  => Scan::whereIn('route_id', $routeIds)->where('scanned_at', '>=', now()->startOfWeek())->count(),
                'total' => Scan::whereIn('route_id', $routeIds)->count(),
            ];
        });
    }

    /**
     * Build the 7-day trend data grouped by operator.
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

        $operators = $raw->pluck('user.name')->unique()->filter()->values()->toArray();

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
