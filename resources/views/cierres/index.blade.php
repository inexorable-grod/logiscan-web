@extends('layouts.app')

@section('title', 'Cierre de Ruta')

@section('content')
<div>

    {{-- Encabezado --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Cierre de Ruta</h1>
        <p class="text-sm text-gray-500 mt-1">Listado de cierres de ruta registrados</p>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ruta</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha Operacion</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Cerrado por</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($closures as $closure)
                <tr class="hover:bg-gray-50 transition-colors">
                    {{-- Ruta --}}
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-800">{{ $closure->route->route_number }}</div>
                        <div class="text-xs text-gray-500">{{ $closure->route->description }}</div>
                    </td>

                    {{-- Fecha Operacion --}}
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ \Carbon\Carbon::parse($closure->operation_date)->format('d/m/Y') }}
                    </td>

                    {{-- Estado --}}
                    <td class="px-6 py-4 text-sm">
                        @switch($closure->status)
                            @case('pending_documents')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                    Esperando documentos
                                </span>
                                @break
                            @case('pending_ocr')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                    Procesando OCR
                                </span>
                                @break
                            @case('pending_review')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                    Revision pendiente
                                </span>
                                @break
                            @case('pending_approval')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                    Aprobacion pendiente
                                </span>
                                @break
                            @case('approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    Aprobado
                                </span>
                                @break
                            @case('rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    Rechazado
                                </span>
                                @break
                        @endswitch
                    </td>

                    {{-- Cerrado por --}}
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $closure->closedBy->name ?? '—' }}
                    </td>

                    {{-- Acciones --}}
                    <td class="px-6 py-4 text-sm">
                        <a href="/cierres/{{ $closure->id }}"
                           class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium transition-colors">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Ver detalle
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <p class="text-sm">No hay cierres de ruta registrados.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginacion --}}
    @if($closures->hasPages())
    <div class="mt-4">
        {{ $closures->links() }}
    </div>
    @endif

</div>
@endsection
