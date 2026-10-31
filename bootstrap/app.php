<?php

use App\Http\Middleware\CheckCanWrite;
use App\Http\Middleware\CheckIsAdmin;
use App\Http\Middleware\EnsureWorkspaceActive;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\ExternalServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'can_write' => CheckCanWrite::class,
            'is_admin' => CheckIsAdmin::class,
            'workspace_active' => EnsureWorkspaceActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Manejo centralizado de fallos en servicios externos y scrapers
        $exceptions->render(function (ExternalServiceException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'mensaje' => $e->getMessage(),
                    'servicio' => $e->getServicio(),
                    'codigo_error' => 'EXTERNAL_SERVICE_UNAVAILABLE',
                ], 503);
            }

            return back()->with('error', $e->getMessage());
        });

        // Manejo centralizado de timeouts y caídas de conexión HTTP
        $exceptions->render(function (ConnectionException $e, Request $request) {
            $mensaje = 'No se pudo conectar con el servicio externo (tiempo de espera agotado o conexión rechazada).';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'mensaje' => $mensaje,
                    'codigo_error' => 'EXTERNAL_CONNECTION_TIMEOUT',
                ], 504);
            }

            return back()->with('error', $mensaje);
        });

        // Manejo centralizado de respuestas HTTP erróneas (4xx, 5xx externas)
        $exceptions->render(function (RequestException $e, Request $request) {
            $status = $e->response?->status() ?? 502;
            $mensaje = "El servicio externo respondió con un error (código HTTP {$status}).";
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'mensaje' => $mensaje,
                    'codigo_error' => 'EXTERNAL_REQUEST_FAILED',
                ], 502);
            }

            return back()->with('error', $mensaje);
        });
    })->create();
