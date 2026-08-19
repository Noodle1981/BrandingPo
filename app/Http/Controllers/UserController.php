<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Listado de usuarios del sistema con sus roles.
     */
    public function index(): Response
    {
        $usuarios = User::orderBy('name')->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'created_at' => $u->created_at?->format('d/m/Y H:i'),
            'is_current' => $u->id === Auth::id(),
        ]);

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles_disponibles' => [
                ['key' => 'admin', 'label' => 'Administrador', 'desc' => 'Control total del sistema y gestión de usuarios.'],
                ['key' => 'consultor', 'label' => 'Consultor Estratégico', 'desc' => 'Carga Fast-Flow, edición de pauta, clipping y analítica.'],
                ['key' => 'visualizador', 'label' => 'Visualizador Ejecutivo', 'desc' => 'Solo lectura. Sin permisos de modificación ni carga.'],
            ],
        ]);
    }

    /**
     * Crear un nuevo usuario en la plataforma.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'consultor', 'visualizador'])],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Ya existe un usuario con este correo.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'role.in' => 'El rol seleccionado no es válido.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Actualizar los datos y rol de un usuario.
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)],
            'role' => ['required', Rule::in(['admin', 'consultor', 'visualizador'])],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar un usuario del sistema.
     */
    public function destroy(User $usuario): RedirectResponse
    {
        if ($usuario->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta de usuario.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado satisfactoriamente.');
    }
}
