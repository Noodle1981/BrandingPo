<?php

namespace App\Http\Requests\Medios;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        $workspace = WorkspaceHelper::activo($this);

        return [
            'medio_prensa_id' => ['required', Rule::exists('medios_prensa', 'id')->where('workspace_id', $workspace->id)],
            'candidato_id' => ['nullable', Rule::exists('candidatos', 'id')->where('workspace_id', $workspace->id)],
            'origen_tipo' => ['nullable', 'in:web,facebook'],
            'tipo_mencion' => ['nullable', 'in:etiqueta_directa,titular,cuerpo'],
            'fecha_publicacion' => ['required', 'date'],
            'titulo' => ['required', 'string', 'max:500'],
            'resumen' => ['nullable', 'string'],
            'url_nota' => ['nullable', 'url', 'max:500'],
            'tono_mencion' => ['required', 'in:favorable,neutro,critico'],
            'puntuacion_sentimiento' => ['nullable', 'numeric', 'between:-1,1'],
            'es_tapa_o_principal' => ['boolean'],
            'interacciones_en_redes_del_medio' => ['nullable', 'integer', 'min:0'],
            'respuesta_replica_candidato' => ['nullable', 'string'],
            'reacciones_desglose' => ['nullable', 'array'],
            'reacciones_desglose.likes' => ['nullable', 'integer', 'min:0'],
            'reacciones_desglose.love' => ['nullable', 'integer', 'min:0'],
            'reacciones_desglose.haha' => ['nullable', 'integer', 'min:0'],
            'reacciones_desglose.wow' => ['nullable', 'integer', 'min:0'],
            'reacciones_desglose.sad' => ['nullable', 'integer', 'min:0'],
            'reacciones_desglose.angry' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'medio_prensa_id.required' => 'El medio de prensa es obligatorio.',
            'fecha_publicacion.required' => 'La fecha de publicación es obligatoria.',
            'titulo.required' => 'El titular de la nota es obligatorio.',
        ];
    }
}
