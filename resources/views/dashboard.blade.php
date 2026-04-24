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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @if(isset($stats['users']))
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['users'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Usuarios Activos</p>
            </div>
            @endif

            @if(isset($stats['centers']))
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['centers'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Centros</p>
            </div>
            @endif

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['routes'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Rutas</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['clients'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Clientes</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_requests'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Solicitudes Pendientes</p>
            </div>
        </div>

        {{-- Scan Stats --}}
        @if(!empty($scanStats))
        <h2 class="text-lg font-semibold text-gray-700 mt-8">Escaneos</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                <p class="text-3xl font-bold text-gray-800">{{ $scanStats['today'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Escaneos Hoy</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-400">
                <p class="text-3xl font-bold text-gray-800">{{ $scanStats['week'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Escaneos Esta Semana</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-300">
                <p class="text-3xl font-bold text-gray-800">{{ $scanStats['total'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Escaneos Totales</p>
            </div>
        </div>
        @endif

        {{-- Scans Per Operator --}}
        @if($scansPerOperator->isNotEmpty())
        <h2 class="text-lg font-semibold text-gray-700 mt-8">Escaneos por Operario (Hoy)</h2>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operario</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Escaneos</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($scansPerOperator as $row)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->user->name ?? 'Desconocido' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">{{ $row->total }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    @endif
</div>
@endsection
