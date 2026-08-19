<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return back()->with('error', 'Acceso denegado: Esta sección requiere rol de Administrador.');
            }
            abort(403, 'Acceso denegado: Se requiere rol de Administrador.');
        }

        return $next($request);
    }
}
