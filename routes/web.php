<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- Rutas Públicas / Autenticación ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Rutas Protegidas (Requieren autenticación) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);

    // Gestión del Perfil Propio (Cliente) y Oposición (Competencia)
    Route::get('/mi-candidato', [\App\Http\Controllers\CandidatoController::class, 'miCandidato'])->name('mi-candidato');
    Route::get('/candidatos', [\App\Http\Controllers\CandidatoController::class, 'index'])->name('candidatos.index');
    Route::get('/candidatos/{candidato}', [\App\Http\Controllers\CandidatoController::class, 'show'])->name('candidatos.show');

    Route::middleware(['can_write'])->group(function () {
        Route::post('/candidatos', [\App\Http\Controllers\CandidatoController::class, 'store'])->name('candidatos.store');
        Route::put('/candidatos/{candidato}', [\App\Http\Controllers\CandidatoController::class, 'update'])->name('candidatos.update');
        Route::delete('/candidatos/{candidato}', [\App\Http\Controllers\CandidatoController::class, 'destroy'])->name('candidatos.destroy');
        Route::post('/perfiles-sociales', [\App\Http\Controllers\CandidatoController::class, 'storePerfilSocial'])->name('perfiles-sociales.store');
        Route::post('/perfiles-sociales/scrape', [\App\Http\Controllers\CandidatoController::class, 'scrapePerfilSocial'])->name('perfiles-sociales.scrape');
        Route::put('/perfiles-sociales/{perfilSocial}', [\App\Http\Controllers\CandidatoController::class, 'updatePerfilSocial'])->name('perfiles-sociales.update');
        Route::delete('/perfiles-sociales/{perfilSocial}', [\App\Http\Controllers\CandidatoController::class, 'destroyPerfilSocial'])->name('perfiles-sociales.destroy');
    });

    // Inteligencia Demográfica & Mapa de Situación Territorial
    Route::get('/territorios', [\App\Http\Controllers\TerritorioController::class, 'index'])->name('territorios.index');
    Route::post('/territorios/auto-detect', [\App\Http\Controllers\TerritorioController::class, 'autoDetect'])->name('territorios.auto-detect');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/territorios', [\App\Http\Controllers\TerritorioController::class, 'store'])->name('territorios.store');
        Route::put('/territorios/{territorio}', [\App\Http\Controllers\TerritorioController::class, 'update'])->name('territorios.update');
    });

    // Feed Social Multired & Fast-Flow Entry Desk
    Route::get('/feed', [\App\Http\Controllers\PublicacionController::class, 'feed'])->name('feed');
    Route::get('/fast-flow', [\App\Http\Controllers\PublicacionController::class, 'fastFlow'])->name('fast-flow');

    Route::middleware(['can_write'])->group(function () {
        Route::post('/fast-flow', [\App\Http\Controllers\PublicacionController::class, 'store'])->name('fast-flow.store');
        Route::put('/publicaciones/{publicacion}', [\App\Http\Controllers\PublicacionController::class, 'update'])->name('publicaciones.update');
        Route::delete('/publicaciones/{publicacion}', [\App\Http\Controllers\PublicacionController::class, 'destroy'])->name('publicaciones.destroy');
    });

    // Observatorio de Medios & Clipping
    Route::get('/medios', [\App\Http\Controllers\MediosController::class, 'index'])->name('medios.index');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/medios/clipping', [\App\Http\Controllers\MediosController::class, 'storeNota'])->name('medios.clipping.store');
        Route::delete('/medios/clipping/{nota}', [\App\Http\Controllers\MediosController::class, 'destroyNota'])->name('medios.clipping.destroy');
    });

    // Centro de Situación de Crisis & Matriz de Alianzas
    Route::get('/crisis', [\App\Http\Controllers\CrisisController::class, 'index'])->name('crisis.index');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/crisis', [\App\Http\Controllers\CrisisController::class, 'storeCrisis'])->name('crisis.store');
        Route::put('/crisis/{crisis}', [\App\Http\Controllers\CrisisController::class, 'updateCrisis'])->name('crisis.update');
        Route::post('/crisis/alianza', [\App\Http\Controllers\CrisisController::class, 'storeAlianza'])->name('crisis.alianza.store');
        Route::delete('/crisis/alianza/{alianza}', [\App\Http\Controllers\CrisisController::class, 'destroyAlianza'])->name('crisis.alianza.destroy');
    });

    // War Room Analytics & Predictor de Pauta
    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/predictor', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('predictor.index');
    Route::post('/analytics/predict', [\App\Http\Controllers\AnalyticsController::class, 'predictApi'])->name('analytics.predict');

    // Calendario & Agenda de Campaña
    Route::get('/calendario', [\App\Http\Controllers\CalendarioController::class, 'index'])->name('calendario.index');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/calendario', [\App\Http\Controllers\CalendarioController::class, 'store'])->name('calendario.store');
        Route::delete('/calendario/{evento}', [\App\Http\Controllers\CalendarioController::class, 'destroy'])->name('calendario.destroy');
    });

    // Control Presupuestario & Finanzas de Campaña
    Route::get('/presupuesto', [\App\Http\Controllers\PresupuestoController::class, 'index'])->name('presupuesto.index');
    Route::middleware(['can_write'])->group(function () {
        Route::post('/presupuesto', [\App\Http\Controllers\PresupuestoController::class, 'store'])->name('presupuesto.store');
        Route::delete('/presupuesto/{partida}', [\App\Http\Controllers\PresupuestoController::class, 'destroy'])->name('presupuesto.destroy');
    });

    // Briefings & Informes Ejecutivos
    Route::get('/briefings', [\App\Http\Controllers\BriefingController::class, 'index'])->name('briefings.index');
    Route::get('/briefing', [\App\Http\Controllers\BriefingController::class, 'index']);
    Route::get('/briefings/{informe}', [\App\Http\Controllers\BriefingController::class, 'show'])->name('briefings.show');
    Route::get('/briefing/{informe}', [\App\Http\Controllers\BriefingController::class, 'show']);
    Route::middleware(['can_write'])->group(function () {
        Route::post('/briefings', [\App\Http\Controllers\BriefingController::class, 'store'])->name('briefings.store');
        Route::post('/briefing', [\App\Http\Controllers\BriefingController::class, 'store']);
    });

    // Gestión de Usuarios (Exclusivo Administrador)
    Route::middleware(['is_admin'])->group(function () {
        Route::resource('usuarios', UserController::class)->except(['create', 'edit', 'show']);
    });
});
