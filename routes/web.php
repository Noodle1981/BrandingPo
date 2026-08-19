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
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    });

    // Gestión de Candidatos y Perfiles Políticos
    Route::get('/candidatos', [\App\Http\Controllers\CandidatoController::class, 'index'])->name('candidatos.index');
    Route::get('/candidatos/{candidato}', [\App\Http\Controllers\CandidatoController::class, 'show'])->name('candidatos.show');

    Route::middleware(['can_write'])->group(function () {
        Route::post('/candidatos', [\App\Http\Controllers\CandidatoController::class, 'store'])->name('candidatos.store');
        Route::put('/candidatos/{candidato}', [\App\Http\Controllers\CandidatoController::class, 'update'])->name('candidatos.update');
        Route::delete('/candidatos/{candidato}', [\App\Http\Controllers\CandidatoController::class, 'destroy'])->name('candidatos.destroy');
    });

    // Feed Social Multired & Fast-Flow Entry Desk
    Route::get('/feed', [\App\Http\Controllers\PublicacionController::class, 'feed'])->name('feed');
    Route::get('/fast-flow', [\App\Http\Controllers\PublicacionController::class, 'fastFlow'])->name('fast-flow');

    Route::middleware(['can_write'])->group(function () {
        Route::post('/fast-flow', [\App\Http\Controllers\PublicacionController::class, 'store'])->name('fast-flow.store');
        Route::delete('/publicaciones/{publicacion}', [\App\Http\Controllers\PublicacionController::class, 'destroy'])->name('publicaciones.destroy');
    });

    // Gestión de Usuarios (Exclusivo Administrador)
    Route::middleware(['is_admin'])->group(function () {
        Route::resource('usuarios', UserController::class)->except(['create', 'edit', 'show']);
    });
});
