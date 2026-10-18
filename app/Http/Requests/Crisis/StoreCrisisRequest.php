<?php

namespace App\Http\Requests\Crisis;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrisisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        $workspace = WorkspaceHelper::activo($this);

        return [
            'candidato_id' => ['required', Rule::exists('candidatos', 'id')->where('workspace_id', $workspace->id)],
            'titulo' => ['required', 'string', 'max:255'],
            'fecha_evento' => ['required', 'date'],
            'nivel_gravedad' => ['required', 'in:leve,moderado,critico'],
            'minutos_tiempo_respuesta' => ['nullable', 'integer', 'min:0'],
            'estrategia_contencion' => ['nullable', 'string'],
            'estado' => ['required', 'in:abierto,en_contencion,resuelto'],
            'impacto_estimado' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'candidato_id.required' => 'El candidato vinculado al incidente es obligatorio.',
            'titulo.required' => 'El título de la crisis es obligatorio.',
            'fecha_evento.required' => 'La fecha del evento es obligatoria.',
            'nivel_gravedad.required' => 'El nivel de gravedad es obligatorio.',
            'estado.required' => 'El estado de la crisis es obligatorio.',
        ];
    }
}
