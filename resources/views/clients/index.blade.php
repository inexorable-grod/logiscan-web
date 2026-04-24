@extends('layouts.app')

@section('title', 'Gestion de Clientes')

@section('content')
<div x-data="{ showCreate: false, showEdit: false, editClient: {} }">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestion de Clientes</h1>
        <button @click="showCreate = true"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow">
            Nuevo Cliente
        </button>
    </div>

    {{-- Filtro por Ruta --}}
    <div class="mb-4">
        <form method="GET" action="/clients" class="flex items-center space-x-3">
            <label class="text-sm font-medium text-gray-700">Filtrar por ruta:</label>
            <select name="route_id" onchange="this.form.submit()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todas las rutas</option>
                @foreach($routes as $route)
                    <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                        {{ $route->route_number }} - {{ $route->description }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Codigo</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Ruta</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Direccion</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Telefono</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($clients as $client)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $client->client_code }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $client->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $client->route->route_number ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $client->address }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $client->phone }}</td>
                    <td class="px-6 py-4 text-sm">
                        <div class="flex items-center gap-1">
                            <button @click="editClient = { id: '{{ $client->id }}', route_id: '{{ $client->route_id }}', client_code: '{{ addslashes($client->client_code) }}', name: '{{ addslashes($client->name) }}', address: '{{ addslashes($client->address) }}', phone: '{{ addslashes($client->phone) }}' }; showEdit = true"
                                    class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-800 transition-colors" title="Editar">
                                <svg class="w-4.5 h-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                            </button>
                            <form action="/clients/{{ $client->id }}" method="POST"
                                  onsubmit="return confirm('¿Esta seguro de eliminar este cliente?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-800 transition-colors" title="Eliminar">
                                    <svg class="w-4.5 h-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">No se encontraron clientes.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginacion --}}
    <div class="mt-4">
        {{ $clients->links() }}
    </div>

    {{-- Modal Crear Cliente --}}
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.outside="showCreate = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Nuevo Cliente</h2>
            <form action="/clients" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ruta</label>
                    <select name="route_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar ruta</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->route_number }} - {{ $route->description }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                    <input type="text" name="client_code" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                    <input type="text" name="address"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="text" name="phone"
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

    {{-- Modal Editar Cliente --}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.outside="showEdit = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Editar Cliente</h2>
            <form :action="'/clients/' + editClient.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ruta</label>
                    <select name="route_id" x-model="editClient.route_id" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar ruta</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->route_number }} - {{ $route->description }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                    <input type="text" name="client_code" x-model="editClient.client_code" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" x-model="editClient.name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                    <input type="text" name="address" x-model="editClient.address"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="text" name="phone" x-model="editClient.phone"
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
