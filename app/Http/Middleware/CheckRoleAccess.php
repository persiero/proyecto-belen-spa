<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRoleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = Auth::user();

        // Verificar permisos según el parámetro recibido
        $hasAccess = match($permission) {
            'almacen' => $user->canAccessAlmacen(),
            'estadisticas' => $user->canAccessEstadisticas(),
            'sistema' => $user->canAccessSistema(),
            default => true
        };

        if (!$hasAccess) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        return $next($request);
    }
}
