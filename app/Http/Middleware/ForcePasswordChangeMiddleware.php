<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChangeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->force_password_change) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Debe cambiar su contraseña antes de continuar.',
                    'action' => 'force_password_change',
                ], 403);
            }

            return redirect('/change-password');
        }

        return $next($request);
    }
}
