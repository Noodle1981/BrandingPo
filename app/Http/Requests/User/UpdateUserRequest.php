<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Reglas de validación aplicables a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User|string|int|null $targetUser */
        $targetUser = $this->route('usuario');
        $userId = $targetUser instanceof User ? $targetUser->id : $targetUser;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'role' => ['required', Rule::in(['admin', 'consultor', 'visualizador'])],
            'password' => ['nullable', 'string', Password::min(8)->letters()->numbers()],
        ];
    }

    /**
     * Mensajes de validación personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Ya existe un usuario con este correo.',
            'role.required' => 'El rol de usuario es obligatorio.',
            'role.in' => 'El rol seleccionado no es válido.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres y contener letras y números.',
        ];
    }
}
