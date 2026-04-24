@extends('layouts.app')

@section('title', 'Gestion de Usuarios')

@section('content')
<div x-data="{ showCreate: false, showEdit: false, editUser: {} }">

    {{-- Encabezado --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestion de Usuarios</h1>
        <button @click="showCreate = true"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow">
            Nuevo Usuario
        </button>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Rol</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $user->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4 text-sm">
                        @switch($user->role)
                            @case('admin')
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">Administrador</span>
                                @break
                            @case('supervisor')
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">Supervisor</span>
                                @break
                            @case('operator')
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Operario</span>
                                @break
                            @case('driver')
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">Conductor</span>
                                @break
                            @default
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">{{ $user->role }}</span>
                        @endswitch
                    </td>
                    <td class="px-6 py-4 text-sm">
                        @if($user->is_active)
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Activo</span>
                        @else
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500">Inactivo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <div class="flex items-center gap-1">
                            {{-- Editar --}}
                            <button @click="editUser = { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}', role: '{{ $user->role }}' }; showEdit = true"
                                    class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-800 transition-colors" title="Editar">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                            </button>
                            {{-- Activar/Desactivar --}}
                            <form action="/admin/users/{{ $user->id }}/toggle" method="POST">
                                @csrf
                                <button type="submit"
                                        class="p-1.5 rounded-lg {{ $user->is_active ? 'text-yellow-600 hover:bg-yellow-50 hover:text-yellow-800' : 'text-green-600 hover:bg-green-50 hover:text-green-800' }} transition-colors"
                                        title="{{ $user->is_active ? 'Desactivar' : 'Activar' }}">
                                    @if($user->is_active)
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    @else
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    @endif
                                </button>
                            </form>
                            {{-- Restablecer Contrasena --}}
                            <form action="/admin/users/{{ $user->id }}/reset-password" method="POST">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('¿Esta seguro de restablecer la contrasena de este usuario?')"
                                        class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-800 transition-colors" title="Restablecer contrasena">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">No se encontraron usuarios.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginacion --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- Modal Crear Usuario --}}
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.outside="showCreate = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Nuevo Usuario</h2>
            <form action="/admin/users" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contrasena</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="role" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Seleccionar rol</option>
                        <option value="admin">Administrador</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="operator">Operario</option>
                        <option value="driver">Conductor</option>
                    </select>
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

    {{-- Modal Editar Usuario --}}
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div @click.outside="showEdit = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Editar Usuario</h2>
            <form :action="'/admin/users/' + editUser.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" x-model="editUser.name" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" x-model="editUser.email" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="role" x-model="editUser.role" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="admin">Administrador</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="operator">Operario</option>
                        <option value="driver">Conductor</option>
                    </select>
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
