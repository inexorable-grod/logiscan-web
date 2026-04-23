@extends('layouts.app')

@section('title', 'Cambiar Contrasena')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800">Cambiar Contrasena</h2>
            <p class="text-sm text-gray-500 mt-1">Actualice su contrasena de acceso al sistema.</p>
        </div>

        <form method="POST" action="/password/change" class="space-y-5">
            @csrf

            {{-- Current Password --}}
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Contrasena Actual</label>
                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    required
                    placeholder="Ingrese su contrasena actual"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition"
                >
                @error('current_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- New Password --}}
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contrasena</label>
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    required
                    placeholder="Ingrese la nueva contrasena"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition"
                >
                @error('new_password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm New Password --}}
            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nueva Contrasena</label>
                <input
                    type="password"
                    id="new_password_confirmation"
                    name="new_password_confirmation"
                    required
                    placeholder="Confirme la nueva contrasena"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition"
                >
                @error('new_password_confirmation')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="pt-2">
                <button
                    type="submit"
                    class="rounded-lg px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 transition"
                    style="background-color: #1a56db;"
                >
                    Cambiar Contrasena
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
