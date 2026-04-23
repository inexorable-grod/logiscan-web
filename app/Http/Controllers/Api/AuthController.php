<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\OperationCenter;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate user and issue Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Esta cuenta ha sido desactivada.'],
            ]);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $token = $user->createToken('mobile')->plainTextToken;

        AuditService::log('LOGIN', 'users', $user->id, null, $user->id);

        $response = [
            'token' => $token,
            'user'  => $user->only('id', 'name', 'email', 'role', 'force_password_change'),
        ];

        if ($user->role === 'supervisor') {
            $response['centers'] = OperationCenter::where('is_active', true)
                ->select('id', 'name', 'code')
                ->get();
        }

        return response()->json($response);
    }

    /**
     * Revoke current token and deactivate device tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        AuditService::log('LOGOUT', 'users', $request->user()->id);

        $request->user()->currentAccessToken()->delete();

        DeviceToken::where('user_id', $request->user()->id)->update(['is_active' => false]);

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
        }

        $user->update([
            'password'              => $request->new_password,
            'force_password_change' => false,
        ]);

        AuditService::log('CHANGE_PASSWORD', 'users', $user->id);

        return response()->json(['message' => 'Contraseña actualizada.']);
    }
}
