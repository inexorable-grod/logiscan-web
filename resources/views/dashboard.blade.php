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
        {{-- General Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @if(isset($stats['users']))
            <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
                <p class="text-2xl font-bold text-gray-800">{{ $stats['users'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Usuarios Activos</p>
            </div>
            @endif

            @if(isset($stats['centers']))
            <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
                <p class="text-2xl font-bold text-gray-800">{{ $stats['centers'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Centros</p>
            </div>
            @endif

            <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
                <p class="text-2xl font-bold text-gray-800">{{ $stats['routes'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Rutas</p>
            </div>

            <div class="bg-white rounded-lg shadow p-5 border-l-4 border-purple-500">
                <p class="text-2xl font-bold text-gray-800">{{ $stats['clients'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Clientes</p>
            </div>

            <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
                <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_requests'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Solicitudes Pendientes</p>
            </div>
        </div>

        {{-- Scan Summary Cards --}}
        @if(!empty($scanStats))
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-indigo-600 rounded-lg shadow p-5 text-white">
                <p class="text-3xl font-bold">{{ $scanStats['today'] }}</p>
                <p class="text-sm text-indigo-200 mt-1">Escaneos Hoy</p>
            </div>
            <div class="bg-indigo-500 rounded-lg shadow p-5 text-white">
                <p class="text-3xl font-bold">{{ $scanStats['week'] }}</p>
                <p class="text-sm text-indigo-200 mt-1">Esta Semana</p>
            </div>
            <div class="bg-indigo-400 rounded-lg shadow p-5 text-white">
                <p class="text-3xl font-bold">{{ $scanStats['total'] }}</p>
                <p class="text-sm text-indigo-200 mt-1">Totales</p>
            </div>
        </div>
        @endif

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Operator Bar Chart --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Escaneos por Operario</h3>
                @php $maxVal = $scansPerOperator->max('total') ?: 1; @endphp
                @forelse ($scansPerOperator as $row)
                <div class="mb-3 group">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                        <span class="truncate mr-2">{{ $row->user->name ?? 'Desconocido' }}</span>
                        <span class="font-semibold text-indigo-600 shrink-0">{{ $row->total }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full bg-indigo-500 transition-all duration-500"
                             style="width: {{ round(($row->total / $maxVal) * 100) }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-8">Sin actividad hoy.</p>
                @endforelse
            </div>

            {{-- Donut Chart: Scans by Type --}}
            <div class="bg-white rounded-xl shadow p-6" x-data="donutChart({{ json_encode($scansByType) }})">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Tipos de Escaneo (Hoy)</h3>
                <template x-if="total > 0">
                    <div class="flex items-center gap-6">
                        <div class="relative flex-shrink-0">
                            <div class="w-28 h-28 rounded-full" :style="'background: conic-gradient(' + gradient + ')'"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center shadow-inner">
                                    <span class="text-lg font-bold text-gray-700" x-text="total"></span>
                                </div>
                            </div>
                        </div>
                        <ul class="space-y-2 text-sm flex-1">
                            <template x-for="item in items" :key="item.label">
                                <li class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" :style="'background:' + item.color"></span>
                                    <span x-text="item.label" class="text-gray-600 capitalize"></span>
                                    <span x-text="item.count" class="ml-auto font-semibold text-gray-800"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>
                <template x-if="total === 0">
                    <p class="text-sm text-gray-400 text-center py-8">Sin escaneos hoy.</p>
                </template>
            </div>

            {{-- 7-Day Trend Chart --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Ultimos 7 Dias</h3>
                @php $maxDay = collect($scansTrend)->max('count') ?: 1; @endphp
                @if(!empty($scansTrend))
                <div class="flex items-end gap-2" style="height: 140px;">
                    @foreach ($scansTrend as $day)
                    @php $heightPct = round(($day['count'] / $maxDay) * 100); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group h-full justify-end">
                        <span class="text-xs font-semibold text-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity">
                            {{ $day['count'] }}
                        </span>
                        <div class="w-full rounded-t bg-indigo-400 group-hover:bg-indigo-500 transition-all duration-300"
                             style="height: {{ max($heightPct, 2) }}%"></div>
                        <span class="text-xs text-gray-400 capitalize">{{ $day['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-8">Sin datos.</p>
                @endif
            </div>

        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('donutChart', (rawData) => ({
        items: [],
        gradient: '',
        total: 0,
        colors: {
            bulto: '#6366f1',
            cubeta: '#8b5cf6',
            rf: '#10b981',
            controlado: '#f59e0b',
            refrigerado: '#06b6d4',
        },
        fallbackColors: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
        init() {
            const entries = Object.entries(rawData || {});
            this.total = entries.reduce((s, [, v]) => s + v, 0);
            if (this.total === 0) return;
            let cumDeg = 0;
            const parts = [];
            this.items = entries.map(([label, count], i) => {
                const color = this.colors[label] || this.fallbackColors[i % this.fallbackColors.length];
                const deg = (count / this.total) * 360;
                parts.push(`${color} ${cumDeg}deg ${cumDeg + deg}deg`);
                cumDeg += deg;
                return { label, count, color };
            });
            this.gradient = parts.join(', ');
        }
    }));
});
</script>
@endpush
@endsection
