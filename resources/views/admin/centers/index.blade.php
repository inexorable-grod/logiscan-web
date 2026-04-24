@extends('layouts.app')

@section('title', 'Gestion de Centros')

@section('content')
<div x-data="{ showCreate: false, showEdit: false, editCenter: {} }">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestion de Centros</h1>
        <button @click="showCreate = true"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow">
            Nuevo Centro
        </button>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Codigo</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Direccion</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Operarios Asignados</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($centers as $center)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $center->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $center->code }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $center->address }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $center->operators_count ?? 0 }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($center->is_active)
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Activo</span>
                        @else
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm space-x-2">
                        <button @click="editCenter = { id: '{{ $center->id }}', name: '{{ addslashes($center->name) }}', code: '{{ addslashes($center->code) }}', address: '{{ addslashes($center->address) }}' }; showEdit = true"
                                class="text-blue-600 hover:text-blue-800 font-medium">
                            Editar
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">No se encontraron centros.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginacion --}}
    <div class="mt-4">
        {{ $centers->links() }}
    </div>

    {{-- Modal Crear Centro --}}
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.outside="showCreate = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Nuevo Centro</h2>
            <form action="/admin/centers" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                    <input type="text" name="code" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                    <input type="text" name="address" required
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

    {{-- Modal Editar Centro --}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.outside="showEdit = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Editar Centro</h2>
            <form :action="'/admin/centers/' + editCenter.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" x-model="editCenter.name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                    <input type="text" name="code" x-model="editCenter.code" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                    <input type="text" name="address" x-model="editCenter.address" required
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
