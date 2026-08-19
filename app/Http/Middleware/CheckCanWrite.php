<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCanWrite
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canWrite()) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return back()->with('error', 'Acción denegada: El rol Visualizador tiene permisos de solo lectura.');
            }
            abort(403, 'Acceso denegado: Tu perfil es de solo lectura.');
        }

        return $next($request);
    }
}
