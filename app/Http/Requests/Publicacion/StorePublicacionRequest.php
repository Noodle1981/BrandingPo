<?php

namespace App\Http\Requests\Publicacion;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicacionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    /**
     * Preparar datos antes de la validación.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'perfil_social_id' => $this->filled('perfil_social_id') ? $this->input('perfil_social_id') : null,
            'eje_tematico_id' => $this->filled('eje_tematico_id') ? $this->input('eje_tematico_id') : null,
        ]);
    }

    /**
     * Reglas de validación aplicables a la petición.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $workspace = WorkspaceHelper::activo($this);

        return [
            'candidato_id' => ['required', Rule::exists('candidatos', 'id')->where('workspace_id', $workspace->id)],
            'perfil_social_id' => ['nullable', 'exists:perfil_socials,id'],
            'plataforma' => ['nullable', 'string'],
            'eje_tematico_id' => ['nullable', Rule::exists('eje_tematicos', 'id')->where('workspace_id', $workspace->id)],
            'eje_tematico_nombre' => ['nullable', 'string', 'max:255'],
            'fecha_publicacion' => ['required', 'date'],
            'tipo_formato' => ['required', 'string'],
            'tipo_pauta' => ['required', 'string', 'in:organico,organico_impulsado,pauta_paga,colaboracion_pagada'],
            'monto_invertido_pauta' => ['nullable', 'numeric', 'min:0'],
            'url_post' => ['nullable', 'string', 'max:1000'],
            'media_url' => ['nullable', 'string', 'max:1000'],
            'vistas_organicas' => ['nullable', 'integer', 'min:0'],
            'vistas_pagadas' => ['nullable', 'integer', 'min:0'],
            'contenido_resumen' => ['required', 'string'],
            'total_likes' => ['nullable', 'integer', 'min:0'],
            'me_gusta' => ['nullable', 'integer', 'min:0'],
            'me_encanta' => ['nullable', 'integer', 'min:0'],
            'me_importa' => ['nullable', 'integer', 'min:0'],
            'me_divierte' => ['nullable', 'integer', 'min:0'],
            'me_asombra' => ['nullable', 'integer', 'min:0'],
            'me_entristece' => ['nullable', 'integer', 'min:0'],
            'me_enoja' => ['nullable', 'integer', 'min:0'],
            'total_comentarios' => ['nullable', 'integer', 'min:0'],
            'total_compartidos' => ['nullable', 'integer', 'min:0'],
            'total_republicados' => ['nullable', 'integer', 'min:0'],
            'total_guardados' => ['nullable', 'integer', 'min:0'],
            'termometro_humor_social' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comentario_destacado' => ['nullable', 'string', 'max:500'],
            'figura_acompanante' => ['nullable', 'string', 'max:255'],
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
            'candidato_id.required' => 'Debes seleccionar un candidato.',
            'contenido_resumen.required' => 'El texto o resumen del post es obligatorio.',
            'fecha_publicacion.required' => 'La fecha de publicación es obligatoria.',
            'tipo_formato.required' => 'El formato de publicación es obligatorio.',
            'tipo_pauta.required' => 'El tipo de pauta es obligatorio.',
        ];
    }
}
