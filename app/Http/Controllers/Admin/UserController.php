<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            User::select('id', 'name', 'email', 'role', 'is_active', 'last_login', 'created_at')
                ->orderBy('name')
                ->paginate(50)
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:gerente_ops,ti_admin,supervisor,operario',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name'                  => $request->name,
            'email'                 => $request->email,
            'password'              => $request->password,
            'role'                  => $request->role,
            'force_password_change' => true,
            'created_by'            => $request->user()->id,
        ]);

        AuditService::log('CREATE_USER', 'users', $user->id, ['role' => $user->role]);

        return response()->json($user, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'role'  => 'sometimes|in:gerente_ops,ti_admin,supervisor,operario',
        ]);

        $user->update($request->only('name', 'email', 'role'));

        AuditService::log('UPDATE_USER', 'users', $user->id);

        return response()->json($user);
    }

    public function deactivate(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => false]);

        AuditService::log('DEACTIVATE_USER', 'users', $user->id);

        return response()->json(['message' => 'Usuario desactivado.']);
    }

    public function resetPassword(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $tempPassword = Str::random(10);

        $user->update([
            'password'              => $tempPassword,
            'force_password_change' => true,
        ]);

        AuditService::log('RESET_PASSWORD', 'users', $user->id);

        return response()->json([
            'message'        => 'Contraseña reseteada.',
            'temp_password'  => $tempPassword,
        ]);
    }
}
