<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica si el usuario autenticado posee un rol específico.
 */
class CheckRole
{
    /**
     * Regla de acceso: si no tiene el rol requerido, retorna 403.
     *
     * @param  string[]  $roles  Lista de roles aceptados (cualquiera otorga acceso).
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        abort(403, __('domain.error.forbidden'));
    }
}
