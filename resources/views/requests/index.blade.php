@extends('layouts.app')

@section('title', 'Solicitudes')

@section('content')
<div x-data="{ showReject: false, rejectId: null }">

    {{-- Encabezado --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Solicitudes</h1>
    </div>

    {{-- Filtros --}}
    <div class="mb-4 bg-white rounded-lg shadow p-4">
        <form method="GET" action="/requests" class="flex flex-wrap items-center gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Estado</label>
                <select name="status" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobada</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazada</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
                <select name="type" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los tipos</option>
                    @if(isset($types))
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Solicitante</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Centro</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($requests as $request)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $request->type }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $request->user->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $request->center->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @switch($request->status)
                            @case('pending')
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">Pendiente</span>
                                @break
                            @case('approved')
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Aprobada</span>
                                @break
                            @case('rejected')
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Rechazada</span>
                                @break
                        @endswitch
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $request->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        @if($request->status === 'pending')
                            <form action="/requests/{{ $request->id }}/approve" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="bg-green-500 hover:bg-green-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">
                                    Aprobar
                                </button>
                            </form>

                            <button @click="rejectId = '{{ $request->id }}'; showReject = true"
                                    class="bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">
                                Rechazar
                            </button>
                        @else
                            <span class="text-gray-400 text-xs">Sin acciones</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">No se encontraron solicitudes.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginacion --}}
    <div class="mt-4">
        {{ $requests->links() }}
    </div>

    {{-- Modal Rechazar Solicitud --}}
    <div x-show="showReject" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.outside="showReject = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Rechazar Solicitud</h2>
            <form :action="'/requests/' + rejectId + '/reject'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo del rechazo <span class="text-red-500">*</span></label>
                    <textarea name="notes" rows="4" required placeholder="Ingrese el motivo del rechazo..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showReject = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg">
                        Confirmar Rechazo
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
