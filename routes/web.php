<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BriefingController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\CandidatoController;
use App\Http\Controllers\CrisisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediosController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\TerritorioController;
use App\Http\Controllers\UserController;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Rutas Públicas / Autenticación ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth');
Route::post('/quick-login', [AuthController::class, 'quickLogin'])->middleware('throttle:auth')->name('quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Rutas Protegidas (Requieren autenticación y Workspace Activo) ---
Route::middleware(['auth', 'workspace_active'])->group(function () {
    // Cambio de Workspace activo (para consultores y administradores)
    Route::post('/workspace/cambiar/{workspace}', function (Request $request, Workspace $workspace) {
        $user = $request->user();
        $tieneAcceso = $user->role === 'admin'
            || $user->workspaces()->where('workspaces.id', $workspace->id)->exists();

        if (! $tieneAcceso) {
            abort(403, 'No tienes acceso a esta campaña / workspace.');
        }

        $user->update(['active_workspace_id' => $workspace->id]);

        return redirect()->back()->with('success', "Campaña cambiada a: {$workspace->nombre}");
    })->name('workspace.cambiar');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Gestión del Perfil Propio (Cliente) y Oposición (Competencia)
    Route::get('/mi-candidato', [CandidatoController::class, 'miCandidato'])->name('mi-candidato');
    Route::get('/candidatos', [CandidatoController::class, 'index'])->name('candidatos.index');
    // IMPORTANTE: benchmarking debe ir ANTES de /candidatos/{candidato} para que Laravel no confunda "benchmarking" como ID
    Route::get('/candidatos/benchmarking', [CandidatoController::class, 'benchmarking'])->name('candidatos.benchmarking');
    Route::get('/candidatos/{candidato}', [CandidatoController::class, 'show'])->name('candidatos.show');
    Route::get('/perfiles-sociales/{perfilSocial}/metricas', [CandidatoController::class, 'metricasCanal'])->name('perfiles-sociales.metricas');

    Route::middleware(['can_write'])->group(function () {
        Route::post('/candidatos', [CandidatoController::class, 'store'])->name('candidatos.store');
        Route::put('/candidatos/{candidato}', [CandidatoController::class, 'update'])->name('candidatos.update');
        Route::delete('/candidatos/{candidato}', [CandidatoController::class, 'destroy'])->name('candidatos.destroy');
        Route::post('/perfiles-sociales', [CandidatoController::class, 'storePerfilSocial'])->name('perfiles-sociales.store');
        Route::post('/perfiles-sociales/scrape', [CandidatoController::class, 'scrapePerfilSocial'])->name('perfiles-sociales.scrape');
        Route::post('/perfiles-sociales/{perfilSocial}/refrescar', [CandidatoController::class, 'refrescarPerfilSocial'])->name('perfiles-sociales.refrescar');
        Route::post('/perfiles-sociales/{perfilSocial}/sincronizar-canal', [CandidatoController::class, 'sincronizarCanal'])->name('perfiles-sociales.sincronizar-canal');
        Route::put('/perfiles-sociales/{perfilSocial}', [CandidatoController::class, 'updatePerfilSocial'])->name('perfiles-sociales.update');
        Route::delete('/perfiles-sociales/{perfilSocial}', [CandidatoController::class, 'destroyPerfilSocial'])->name('perfiles-sociales.destroy');
    });

    // Inteligencia Demográfica & Mapa de Situación Territorial
    Route::get('/territorios', [TerritorioController::class, 'index'])->name('territorios.index');
    Route::get('/territorios/impacto-electoral', [TerritorioController::class, 'impactoElectoral'])->name('territorios.impacto-electoral');
    Route::post('/territorios/auto-detect', [TerritorioController::class, 'autoDetect'])->name('territorios.auto-detect');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/territorios', [TerritorioController::class, 'store'])->name('territorios.store');
        Route::put('/territorios/{territorio}', [TerritorioController::class, 'update'])->name('territorios.update');
    });

    // Feed Social Multired
    Route::get('/feed', [PublicacionController::class, 'feed'])->name('feed');

    Route::middleware(['can_write'])->group(function () {
        Route::post('/publicaciones', [PublicacionController::class, 'store'])->name('publicaciones.store');
        Route::post('/publicaciones/scrape-post', [PublicacionController::class, 'scrapePost'])->name('publicaciones.scrape-post');
        Route::post('/publicaciones/{publicacion}/sincronizar', [PublicacionController::class, 'sincronizarIndividual'])->name('publicaciones.sincronizar');
        Route::post('/perfiles-sociales/{perfilSocial}/sincronizar-recientes', [PublicacionController::class, 'sincronizarRecientes'])->name('perfiles-sociales.sincronizar-recientes');
        Route::put('/publicaciones/{publicacion}', [PublicacionController::class, 'update'])->name('publicaciones.update');
        Route::patch('/publicaciones/{publicacion}/fecha', [PublicacionController::class, 'actualizarFecha'])->name('publicaciones.fecha');
        Route::delete('/publicaciones/{publicacion}', [PublicacionController::class, 'destroy'])->name('publicaciones.destroy');
    });

    // Observatorio de Medios & Clipping
    Route::get('/medios', [MediosController::class, 'index'])->name('medios.index');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/medios/clipping', [MediosController::class, 'storeNota'])->name('medios.clipping.store');
        Route::delete('/medios/clipping/{nota}', [MediosController::class, 'destroyNota'])->name('medios.clipping.destroy');
    });

    // Centro de Situación de Crisis & Matriz de Alianzas
    Route::get('/crisis', [CrisisController::class, 'index'])->name('crisis.index');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/crisis', [CrisisController::class, 'storeCrisis'])->name('crisis.store');
        Route::put('/crisis/{crisis}', [CrisisController::class, 'updateCrisis'])->name('crisis.update');
        Route::post('/crisis/alianza', [CrisisController::class, 'storeAlianza'])->name('crisis.alianza.store');
        Route::delete('/crisis/alianza/{alianza}', [CrisisController::class, 'destroyAlianza'])->name('crisis.alianza.destroy');
    });

    // War Room Analytics & Predictor de Pauta
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/predictor', [AnalyticsController::class, 'index'])->name('predictor.index');
    Route::post('/analytics/predict', [AnalyticsController::class, 'predictApi'])->name('analytics.predict');

    // Calendario & Agenda de Campaña
    Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario.index');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/calendario', [CalendarioController::class, 'store'])->name('calendario.store');
        Route::delete('/calendario/{evento}', [CalendarioController::class, 'destroy'])->name('calendario.destroy');
    });

    // Control Presupuestario & Finanzas de Campaña
    Route::get('/presupuesto', [PresupuestoController::class, 'index'])->name('presupuesto.index');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/presupuesto', [PresupuestoController::class, 'store'])->name('presupuesto.store');
        Route::delete('/presupuesto/{partida}', [PresupuestoController::class, 'destroy'])->name('presupuesto.destroy');
    });

    // Briefings & Informes Ejecutivos
    Route::get('/briefings', [BriefingController::class, 'index'])->name('briefings.index');
    Route::get('/briefing', [BriefingController::class, 'index']);
    Route::get('/briefings/{informe}', [BriefingController::class, 'show'])->name('briefings.show');
    Route::get('/briefing/{informe}', [BriefingController::class, 'show']);
    Route::middleware(['can_write'])->group(function () {
        Route::post('/briefings', [BriefingController::class, 'store'])->name('briefings.store');
        Route::post('/briefing', [BriefingController::class, 'store']);
    });

    // Gestión de Usuarios (Exclusivo Administrador)
    Route::middleware(['is_admin'])->group(function () {
        Route::resource('usuarios', UserController::class)->except(['create', 'edit', 'show']);
    });
});
