<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verificamos si el usuario está autenticado
        // 2. Comprobamos si su campo 'rol' es exactamente 'admin'
        if ($request->user() && $request->user()->rol === 'admin') {
            return $next($request);
        }

        // Si no es admin, le devolvemos un error de "No autorizado"
        return response()->json([
            'message' => 'Acceso denegado. Se requieren privilegios de administrador.'
        ], 403);
    }
}
