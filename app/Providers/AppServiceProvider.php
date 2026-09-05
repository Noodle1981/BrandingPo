<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Verificación de APP_KEY
        if (empty(config('app.key'))) {
            throw new RuntimeException('APP_KEY no está configurada. Ejecuta: php artisan key:generate');
        }

        // 2. Forzar HTTPS en entorno de producción
        if (app()->isProduction()) {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }

        // 3. Configuración de Rate Limiters de seguridad
        RateLimiter::for('auth', function (Request $request) {
            $maxAttempts = app()->isProduction() ? 5 : 30;
            return Limit::perMinute($maxAttempts)->by($request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'email' => 'Demasiados intentos de acceso. Por seguridad, espera 1 minuto antes de reintentar.',
                    ]);
                });
        });

        RateLimiter::for('scraping', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
