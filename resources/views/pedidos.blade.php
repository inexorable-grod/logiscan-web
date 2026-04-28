@extends('layouts.app')

@section('title', 'Pedidos / MACH')

@section('content')
<div class="space-y-6">

    {{-- Top bar: Route selector + Date --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="/pedidos" class="flex flex-col sm:flex-row items-end gap-4">
            <div class="w-full sm:w-1/3">
                <label for="route_id" class="block text-sm font-medium text-gray-700 mb-1">Ruta</label>
                <select name="route_id" id="route_id"
                        class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Seleccione una ruta --</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" {{ (string) $selectedRouteId === (string) $route->id ? 'selected' : '' }}>
                            {{ $route->route_number }} - {{ $route->description }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-1/4">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                <input type="date" name="date" id="date" value="{{ $selectedDate }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    Ver comparativo
                </button>
            </div>
        </form>
    </div>

    @if($comparison !== null)

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Pedidos --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $comparison['summary']['total_orders'] }}</p>
                        <p class="text-xs text-gray-500">Total Pedidos</p>
                    </div>
                </div>
            </div>

            {{-- Completos --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $comparison['summary']['complete'] }}</p>
                        <p class="text-xs text-gray-500">Completos</p>
                    </div>
                </div>
            </div>

            {{-- Incompletos --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $comparison['summary']['incomplete'] }}</p>
                        <p class="text-xs text-gray-500">Incompletos</p>
                    </div>
                </div>
            </div>

            {{-- Pendientes --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800">{{ $comparison['summary']['pending'] }}</p>
                        <p class="text-xs text-gray-500">Pendientes</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Comparison Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Comparativo de Pedidos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Pedido</th>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Cubetas</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Cajas/Bolsa</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Refr.</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Cont.</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Progreso</th>
                            <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($comparison['orders'] as $order)
                            @php
                                $scanned = $order['scanned'];
                                $expected = $order['expected'];
                                $pct = $expected['total'] > 0 ? round(($scanned['total'] / $expected['total']) * 100) : 0;
                                $pct = min($pct, 100);
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-800">{{ $order['pedido_number'] }}</td>
                                <td class="px-6 py-3 text-gray-600">
                                    <div>{{ $order['client_name'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $order['client_code'] }}</div>
                                </td>

                                {{-- Cubetas --}}
                                <td class="px-4 py-3 text-center">
                                    @php $s = $scanned['cubetas']; $e = $expected['cubetas']; @endphp
                                    <span class="{{ $e == 0 ? 'text-gray-400' : ($s >= $e ? 'text-green-600 font-semibold' : ($s > 0 ? 'text-amber-600 font-semibold' : 'text-red-600 font-semibold')) }}">
                                        {{ $s }}/{{ $e }}
                                    </span>
                                </td>

                                {{-- Cajas/Bolsa --}}
                                <td class="px-4 py-3 text-center">
                                    @php $s = $scanned['cajas_bolsa']; $e = $expected['cajas_bolsa']; @endphp
                                    <span class="{{ $e == 0 ? 'text-gray-400' : ($s >= $e ? 'text-green-600 font-semibold' : ($s > 0 ? 'text-amber-600 font-semibold' : 'text-red-600 font-semibold')) }}">
                                        {{ $s }}/{{ $e }}
                                    </span>
                                </td>

                                {{-- Refrigerado --}}
                                <td class="px-4 py-3 text-center">
                                    @php $s = $scanned['refrigerado']; $e = $expected['refrigerado']; @endphp
                                    <span class="{{ $e == 0 ? 'text-gray-400' : ($s >= $e ? 'text-green-600 font-semibold' : ($s > 0 ? 'text-amber-600 font-semibold' : 'text-red-600 font-semibold')) }}">
                                        {{ $s }}/{{ $e }}
                                    </span>
                                </td>

                                {{-- Controlado --}}
                                <td class="px-4 py-3 text-center">
                                    @php $s = $scanned['controlado']; $e = $expected['controlado']; @endphp
                                    <span class="{{ $e == 0 ? 'text-gray-400' : ($s >= $e ? 'text-green-600 font-semibold' : ($s > 0 ? 'text-amber-600 font-semibold' : 'text-red-600 font-semibold')) }}">
                                        {{ $s }}/{{ $e }}
                                    </span>
                                </td>

                                {{-- Total --}}
                                <td class="px-4 py-3 text-center">
                                    @php $s = $scanned['total']; $e = $expected['total']; @endphp
                                    <span class="font-bold {{ $s >= $e ? 'text-green-600' : ($s > 0 ? 'text-amber-600' : 'text-red-600') }}">
                                        {{ $s }}/{{ $e }}
                                    </span>
                                </td>

                                {{-- Progress Bar --}}
                                <td class="px-4 py-3">
                                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden" style="min-width: 80px;">
                                        <div class="h-2 rounded-full transition-all duration-300
                                            {{ $pct >= 100 ? 'bg-green-500' : ($pct > 0 ? 'bg-amber-500' : 'bg-red-300') }}"
                                            style="width: {{ $pct }}%;">
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-400 text-center mt-1">{{ $pct }}%</p>
                                </td>

                                {{-- Estado --}}
                                <td class="px-6 py-3 text-center">
                                    @if($order['status'] === 'complete')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Completo</span>
                                    @elseif($order['status'] === 'incomplete')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Incompleto</span>
                                    @elseif($order['status'] === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Pendiente</span>
                                    @elseif($order['status'] === 'over')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Excedente</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Unmatched Scans --}}
        @if(!empty($comparison['unmatched_scans']))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Escaneos sin pedido asignado</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Codigo de Barra</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($comparison['unmatched_scans'] as $index => $scan)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-3 text-gray-400">{{ $index + 1 }}</td>
                                    <td class="px-6 py-3 font-medium text-gray-800">{{ $scan['barcode'] ?? $scan['code'] ?? '-' }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $scan['type'] ?? '-' }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $scan['scanned_at'] ?? $scan['datetime'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @elseif($selectedRouteId)
        {{-- No closure found --}}
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded shadow">
            <p class="text-yellow-800 text-lg">No se encontro un cierre para esta ruta en la fecha seleccionada.</p>
        </div>
    @else
        {{-- No route selected --}}
        <div class="bg-blue-50 border-l-4 border-blue-400 p-6 rounded shadow">
            <p class="text-blue-800 text-lg">Seleccione una ruta y fecha para ver el comparativo.</p>
        </div>
    @endif

</div>
@endsection
