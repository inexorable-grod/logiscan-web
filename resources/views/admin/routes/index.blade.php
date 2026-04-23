@extends('layouts.app')

@section('title', 'Gestion de Rutas')

@section('content')
<div x-data="{ showCreate: false, showEdit: false, editRoute: {} }">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestion de Rutas</h1>
        <button @click="showCreate = true"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow">
            Nueva Ruta
        </button>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Numero de Ruta</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Descripcion</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Centro</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($routes as $route)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $route->route_number }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $route->description }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $route->center->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($route->is_active)
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Activo</span>
                        @else
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        <button @click="editRoute = { id: {{ $route->id }}, center_id: {{ $route->center_id }}, route_number: '{{ addslashes($route->route_number) }}', description: '{{ addslashes($route->description) }}' }; showEdit = true"
                                class="text-blue-600 hover:text-blue-800 font-medium">
                            Editar
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">No se encontraron rutas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginacion --}}
    <div class="mt-4">
        {{ $routes->links() }}
    </div>

    {{-- Modal Crear Ruta --}}
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.outside="showCreate = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Nueva Ruta</h2>
            <form action="/admin/routes" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Centro</label>
                    <select name="center_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar centro</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}">{{ $center->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero de Ruta</label>
                    <input type="text" name="route_number" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripcion</label>
                    <input type="text" name="description"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showCreate = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Editar Ruta --}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.outside="showEdit = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Editar Ruta</h2>
            <form :action="'/admin/routes/' + editRoute.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Centro</label>
                    <select name="center_id" x-model="editRoute.center_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar centro</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}">{{ $center->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero de Ruta</label>
                    <input type="text" name="route_number" x-model="editRoute.route_number" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripcion</label>
                    <input type="text" name="description" x-model="editRoute.description"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3 pt-2">
                    <button type="button" @click="showEdit = false"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg">
                        Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
