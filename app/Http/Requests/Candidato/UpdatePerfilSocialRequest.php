<?php

namespace App\Http\Requests\Candidato;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePerfilSocialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        return [
            'handle_usuario' => ['required', 'string', 'max:255'],
            'url_perfil' => ['nullable', 'url', 'max:1000'],
            'foto_perfil_url' => ['nullable', 'url', 'max:1000'],
            'esta_activo' => ['required', 'boolean'],
            'esta_verificado' => ['required', 'boolean'],
            'seguidores_actuales' => ['nullable', 'integer', 'min:0'],
            'seguidos_actuales' => ['nullable', 'integer', 'min:0'],
            'publicaciones_totales' => ['nullable', 'integer', 'min:0'],
            'me_gusta_totales' => ['nullable', 'integer', 'min:0'],
            'visualizaciones_totales' => ['nullable', 'integer', 'min:0'],
            'fecha_punto_cero' => ['nullable', 'date'],
            'seguidores_punto_cero' => ['nullable', 'integer', 'min:0'],
            'seguidos_punto_cero' => ['nullable', 'integer', 'min:0'],
            'publicaciones_punto_cero' => ['nullable', 'integer', 'min:0'],
            'me_gusta_punto_cero' => ['nullable', 'integer', 'min:0'],
            'visualizaciones_punto_cero' => ['nullable', 'integer', 'min:0'],
            'notas_punto_cero' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'handle_usuario.required' => 'El handle de usuario es obligatorio.',
        ];
    }
}
