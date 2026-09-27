<?php

namespace App\Http\Requests\Publicacion;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePublicacionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
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
            'contenido_resumen' => ['required', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'url_post' => ['nullable', 'string', 'max:1000'],
            'media_url' => ['nullable', 'string', 'max:1000'],
            'tipo_formato' => ['required', 'string'],
            'tipo_pauta' => ['required', 'string', 'in:organico,organico_impulsado,pauta_paga,colaboracion_pagada'],
            'monto_invertido_pauta' => ['nullable', 'numeric', 'min:0'],
            'vistas_organicas' => ['nullable', 'integer', 'min:0'],
            'vistas_pagadas' => ['nullable', 'integer', 'min:0'],
            'total_vistas' => ['nullable', 'integer', 'min:0'],
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
            'eje_tematico_id' => ['nullable', Rule::exists('eje_tematicos', 'id')->where('workspace_id', $workspace->id)],
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
            'contenido_resumen.required' => 'El texto o resumen del post es obligatorio.',
            'tipo_formato.required' => 'El formato de publicación es obligatorio.',
            'tipo_pauta.required' => 'El tipo de pauta es obligatorio.',
        ];
    }
}
