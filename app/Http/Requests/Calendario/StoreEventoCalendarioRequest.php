<?php

namespace App\Http\Requests\Calendario;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventoCalendarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        $workspace = WorkspaceHelper::activo($this);

        return [
            'ciclo_campana_id' => ['required', Rule::exists('ciclo_campanas', 'id')->where('workspace_id', $workspace->id)],
            'candidato_id' => ['nullable', Rule::exists('candidatos', 'id')->where('workspace_id', $workspace->id)],
            'eje_tematico_id' => ['nullable', Rule::exists('eje_tematicos', 'id')->where('workspace_id', $workspace->id)],
            'titulo' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'tipo_evento' => ['required', 'string'],
            'lugar' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:programado,realizado,cancelado'],
            'notas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'ciclo_campana_id.required' => 'El ciclo de campaña es obligatorio.',
            'titulo.required' => 'El título del evento es obligatorio.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'tipo_evento.required' => 'El tipo de evento es obligatorio.',
            'estado.required' => 'El estado del evento es obligatorio.',
        ];
    }
}
