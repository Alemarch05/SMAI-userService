<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Verifica que el usuario autenticado posea alguno de los roles permitidos.
     *
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user('api') ?? $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autenticado.',
            ], 401);
        }

        if ($roles === []) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acceso denegado. No se especificaron roles permitidos.',
            ], 403);
        }

        $user->loadMissing('role');
        $userRole = $user->role?->name;

        if (! is_string($userRole) || trim($userRole) === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Acceso denegado. El usuario no tiene un rol asignado.',
            ], 403);
        }

        $allowedRoles = array_map(
            static fn (string $role): string => strtolower(trim($role)),
            $roles,
        );

        if (! in_array(strtolower(trim($userRole)), $allowedRoles, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acceso denegado. No tienes permisos suficientes para realizar esta acción.',
            ], 403);
        }

        return $next($request);
    }
}
