<?php

namespace App\Http\Middleware;

use App\Models\CenterUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CenterAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        // TI y Gerente: acceso global
        if (in_array($user->role, ['ti_admin', 'gerente_ops'])) {
            return $next($request);
        }

        // Supervisor: requiere centro activo en sesión
        if ($user->role === 'supervisor') {
            $activeCenterId = session('active_center_id');
            if (!$activeCenterId) {
                return $request->expectsJson()
                    ? response()->json(['error' => 'Centro no seleccionado.', 'action' => 'select_center'], 403)
                    : redirect('/select-center');
            }
            $request->merge(['active_center_id' => $activeCenterId]);
            return $next($request);
        }

        // Operarios: verificar asignación activa
        $assignment = CenterUser::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('center')
            ->first();

        if (!$assignment) {
            return response()->json([
                'error' => 'Sin acceso — contacta al supervisor o TI.',
            ], 403);
        }

        $request->merge([
            'active_center' => $assignment->center,
            'active_center_id' => $assignment->center_id,
        ]);

        return $next($request);
    }
}
