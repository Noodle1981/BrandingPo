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

    // Gestión de Usuarios (Exclusivo Administrador)
    Route::middleware(['is_admin'])->group(function () {
        Route::resource('usuarios', UserController::class)->except(['create', 'edit', 'show']);
    });
});
