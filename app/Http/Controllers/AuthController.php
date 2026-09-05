<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Mostrar la pantalla de inicio de sesión.
     */
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    /**
     * Procesar intento de autenticación estándar.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debes ingresar un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            return redirect()->intended(route('dashboard'))
                ->with('success', "¡Bienvenido a la Sala de Situación, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    /**
     * Acceso rápido de demostración para evaluación por rol.
     */
    public function quickLogin(Request $request): RedirectResponse
    {
        if (app()->isProduction()) {
            abort(403, 'El acceso rápido de demostración está deshabilitado en entorno de producción.');
        }

        $validated = $request->validate([
            'role' => ['nullable', 'string', Rule::in(['admin', 'consultor', 'visualizador'])],
        ]);

        $role = $validated['role'] ?? 'admin';
        $user = User::where('role', $role)->first();

        if (! $user) {
            // Si no existe, crear usuario al vuelo
            $user = User::create([
                'name' => match ($role) {
                    'admin' => 'Administrador General',
                    'consultor' => 'Consultor Estratégico',
                    default => 'Visualizador Ejecutivo',
                },
                'email' => "{$role}@brandingpo.com",
                'password' => bcrypt('password'),
                'role' => $role,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $badgeText = match ($role) {
            'admin' => 'Administrador (Control Total)',
            'consultor' => 'Consultor (Operativo y Carga)',
            default => 'Visualizador (Solo Lectura)',
        };

        return redirect()->route('dashboard')
            ->with('success', "Has iniciado sesión con perfil {$badgeText}.");
    }

    /**
     * Cerrar la sesión del usuario.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Has cerrado sesión correctamente.');
    }
}
