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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Usuarios Activos --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['users'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Usuarios Activos</p>
            </div>

            {{-- Centros --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['centers'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Centros</p>
            </div>

            {{-- Rutas --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['routes'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Rutas</p>
            </div>

            {{-- Clientes --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['clients'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Clientes</p>
            </div>

            {{-- Solicitudes Pendientes --}}
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <p class="text-3xl font-bold text-gray-800">{{ $stats['pending_requests'] ?? 0 }}</p>
                <p class="text-sm text-gray-500 mt-1">Solicitudes Pendientes</p>
            </div>
        </div>
    @endif
</div>
@endsection
