<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            return $request->expectsJson()
                ? response()->json(['error' => 'No tiene permisos para esta acción.'], 403)
                : abort(403, 'No tiene permisos para esta acción.');
        }

        return $next($request);
    }
}
