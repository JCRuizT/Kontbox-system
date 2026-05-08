<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que verifica si el usuario autenticado posee un permiso específico.
 */
class CheckPermission
{
    /**
     * Regla de acceso: si no tiene el permiso, retorna 403.
     *
     * @param  string  $permission  Nombre del permiso requerido.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->hasPermissionTo($permission)) {
            abort(403, __('domain.error.forbidden_action'));
        }

        return $next($request);
    }
}
