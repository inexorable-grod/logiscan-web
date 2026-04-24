@extends('layouts.app')

@section('title', 'Panel de Control')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Bienvenido, {{ auth()->user()->name }}</h1>

    @if(empty($stats))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded shadow">
            <p class="text-yellow-800 text-lg">Seleccione un centro de operacion para ver las estadisticas.</p>
        </div>
    @else
        {{-- Row 1: Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @if(isset($stats['users']))
            <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['users'] }}</p>
                    <p class="text-xs text-gray-500">Usuarios</p>
                </div>
            </div>
            @endif

            @if(isset($stats['centers']))
            <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M3.75 3v18m16.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['centers'] }}</p>
                    <p class="text-xs text-gray-500">Centros</p>
                </div>
            </div>
            @endif

            <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['routes'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Rutas</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['clients'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Clientes</p>
                </div>
            </div>
        </div>

        {{-- Row 2: Scan summary + Pending requests --}}
        @if(!empty($scanStats))
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-indigo-600 rounded-lg shadow p-4 text-white">
                <p class="text-3xl font-bold">{{ $scanStats['today'] }}</p>
                <p class="text-xs text-indigo-200 mt-1">Escaneos Hoy</p>
            </div>
            <div class="bg-indigo-500 rounded-lg shadow p-4 text-white">
                <p class="text-3xl font-bold">{{ $scanStats['week'] }}</p>
                <p class="text-xs text-indigo-200 mt-1">Esta Semana</p>
            </div>
            <div class="bg-indigo-400 rounded-lg shadow p-4 text-white">
                <p class="text-3xl font-bold">{{ $scanStats['total'] }}</p>
                <p class="text-xs text-indigo-200 mt-1">Total Escaneos</p>
            </div>
            <div class="bg-red-500 rounded-lg shadow p-4 text-white">
                <p class="text-3xl font-bold">{{ $stats['pending_requests'] ?? 0 }}</p>
                <p class="text-xs text-red-200 mt-1">Solicitudes Pendientes</p>
            </div>
        </div>
        @endif

        {{-- Row 3: Main Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 7-Day Stacked Bar Chart by Operator (spans 2 cols) --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow p-6"
                 x-data="stackedChart({{ json_encode($scansTrendByOperator) }})">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Escaneos — Ultimos 7 Dias</h3>
                <p class="text-xs text-gray-400 mb-4">Agrupados por operario</p>

                <template x-if="hasData">
                    <div>
                        {{-- Legend --}}
                        <div class="flex flex-wrap gap-3 mb-4">
                            <template x-for="(op, i) in operators" :key="op">
                                <div class="flex items-center gap-1.5 text-xs">
                                    <span class="w-3 h-3 rounded" :style="'background:' + getColor(i)"></span>
                                    <span class="text-gray-600" x-text="op"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Bars --}}
                        <div class="flex items-end gap-3" style="height: 200px;">
                            <template x-for="day in days" :key="day.date">
                                <div class="flex-1 flex flex-col items-center h-full justify-end group">
                                    {{-- Tooltip --}}
                                    <span class="text-xs font-semibold text-gray-700 opacity-0 group-hover:opacity-100 transition-opacity mb-1"
                                          x-text="dayTotal(day)"></span>
                                    {{-- Stacked segments --}}
                                    <div class="w-full flex flex-col-reverse rounded-t overflow-hidden"
                                         :style="'height:' + dayPct(day) + '%'">
                                        <template x-for="(op, i) in operators" :key="op">
                                            <div :style="'height:' + segmentPct(day, op) + '%; background:' + getColor(i)"
                                                 class="w-full min-h-0 transition-all duration-300"></div>
                                        </template>
                                    </div>
                                    {{-- Label --}}
                                    <div class="text-center mt-1.5">
                                        <span class="text-xs text-gray-400 capitalize block" x-text="day.label"></span>
                                        <span class="text-[10px] text-gray-300" x-text="day.dayNum"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                <template x-if="!hasData">
                    <p class="text-sm text-gray-400 text-center py-12">Sin escaneos en los ultimos 7 dias.</p>
                </template>
            </div>

            {{-- Donut Chart: Package Types --}}
            <div class="bg-white rounded-xl shadow p-6" x-data="donutChart({{ json_encode($scansByType) }}, 'types')">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Tipos de Paquete</h3>
                <p class="text-xs text-gray-400 mb-4">Escaneos de hoy</p>
                <template x-if="total > 0">
                    <div class="flex flex-col items-center gap-4">
                        <div class="relative">
                            <div class="w-36 h-36 rounded-full" :style="'background: conic-gradient(' + gradient + ')'"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center shadow-inner">
                                    <div class="text-center">
                                        <span class="text-xl font-bold text-gray-700 block" x-text="total"></span>
                                        <span class="text-[10px] text-gray-400">total</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ul class="w-full space-y-2 text-sm">
                            <template x-for="item in items" :key="item.label">
                                <li class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" :style="'background:' + item.color"></span>
                                    <span x-text="item.displayLabel" class="text-gray-600"></span>
                                    <span class="flex-1 border-b border-dotted border-gray-200 mx-1"></span>
                                    <span x-text="item.count" class="font-semibold text-gray-800"></span>
                                    <span class="text-gray-400 text-xs" x-text="'(' + item.pct + '%)'"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
                <template x-if="total === 0">
                    <p class="text-sm text-gray-400 text-center py-12">Sin escaneos hoy.</p>
                </template>
            </div>
        </div>

        {{-- Row 4: Secondary Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Clients per Route --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Clientes por Ruta</h3>
                <p class="text-xs text-gray-400 mb-4">Top 10 rutas</p>
                @php $maxClients = $clientsPerRoute->max('total') ?: 1; @endphp
                @forelse ($clientsPerRoute as $row)
                <div class="mb-3 group">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span>Ruta {{ $row->route->route_number ?? '?' }}</span>
                        <span class="font-semibold text-purple-600">{{ $row->total }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                        <div class="h-2.5 rounded-full bg-purple-500 transition-all duration-500"
                             style="width: {{ round(($row->total / $maxClients) * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-8">Sin datos.</p>
                @endforelse
            </div>

            {{-- Requests by Status --}}
            <div class="bg-white rounded-xl shadow p-6" x-data="donutChart({{ json_encode($requestsByStatus) }}, 'requests')">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Solicitudes</h3>
                <p class="text-xs text-gray-400 mb-4">Distribucion por estado</p>
                <template x-if="total > 0">
                    <div class="flex items-center gap-8">
                        <div class="relative flex-shrink-0">
                            <div class="w-32 h-32 rounded-full" :style="'background: conic-gradient(' + gradient + ')'"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-18 h-18 rounded-full bg-white flex items-center justify-center shadow-inner" style="width: 72px; height: 72px;">
                                    <div class="text-center">
                                        <span class="text-lg font-bold text-gray-700 block" x-text="total"></span>
                                        <span class="text-[10px] text-gray-400">total</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ul class="space-y-3 text-sm flex-1">
                            <template x-for="item in items" :key="item.label">
                                <li class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" :style="'background:' + item.color"></span>
                                    <span x-text="item.displayLabel" class="text-gray-600"></span>
                                    <span class="flex-1"></span>
                                    <span x-text="item.count" class="font-bold text-gray-800 text-lg"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
                <template x-if="total === 0">
                    <p class="text-sm text-gray-400 text-center py-8">Sin solicitudes.</p>
                </template>
            </div>

        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const OPERATOR_COLORS = [
        '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#14b8a6'
    ];

    const TYPE_LABELS = {
        bulto: 'Bulto', cubeta: 'Cubeta', rf: 'RF',
        controlado: 'Controlado', refrigerado: 'Refrigerado'
    };

    const TYPE_COLORS = {
        bulto: '#6366f1', cubeta: '#8b5cf6', rf: '#10b981',
        controlado: '#f59e0b', refrigerado: '#06b6d4'
    };

    const STATUS_LABELS = {
        pending: 'Pendientes', approved: 'Aprobadas', rejected: 'Rechazadas'
    };

    const STATUS_COLORS = {
        pending: '#f59e0b', approved: '#10b981', rejected: '#ef4444'
    };

    // Stacked bar chart for 7-day trend by operator
    Alpine.data('stackedChart', (data) => ({
        operators: data.operators || [],
        days: data.days || [],
        maxDayTotal: 0,
        hasData: false,
        init() {
            this.maxDayTotal = Math.max(...this.days.map(d => this.dayTotal(d)), 1);
            this.hasData = this.days.some(d => this.dayTotal(d) > 0);
        },
        getColor(i) { return OPERATOR_COLORS[i % OPERATOR_COLORS.length]; },
        dayTotal(day) {
            return Object.values(day.operators || {}).reduce((s, v) => s + v, 0);
        },
        dayPct(day) {
            return Math.max(Math.round((this.dayTotal(day) / this.maxDayTotal) * 100), 0);
        },
        segmentPct(day, op) {
            const dt = this.dayTotal(day);
            if (dt === 0) return 0;
            return Math.round(((day.operators[op] || 0) / dt) * 100);
        }
    }));

    // Reusable donut chart
    Alpine.data('donutChart', (rawData, mode) => ({
        items: [],
        gradient: '',
        total: 0,
        init() {
            const entries = Object.entries(rawData || {});
            this.total = entries.reduce((s, [, v]) => s + v, 0);
            if (this.total === 0) return;

            const colors = mode === 'requests' ? STATUS_COLORS : TYPE_COLORS;
            const labels = mode === 'requests' ? STATUS_LABELS : TYPE_LABELS;
            const fallback = OPERATOR_COLORS;

            let cumDeg = 0;
            const parts = [];
            this.items = entries.map(([label, count], i) => {
                const color = colors[label] || fallback[i % fallback.length];
                const deg = (count / this.total) * 360;
                parts.push(`${color} ${cumDeg}deg ${cumDeg + deg}deg`);
                cumDeg += deg;
                return {
                    label,
                    displayLabel: labels[label] || label,
                    count,
                    color,
                    pct: Math.round((count / this.total) * 100)
                };
            });
            this.gradient = parts.join(', ');
        }
    }));
});
</script>
@endpush
@endsection
