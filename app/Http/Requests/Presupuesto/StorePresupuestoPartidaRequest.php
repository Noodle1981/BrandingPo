<?php

namespace App\Http\Requests\Presupuesto;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePresupuestoPartidaRequest extends FormRequest
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
            'categoria' => ['required', 'string'],
            'monto_asignado' => ['required', 'numeric', 'min:0'],
            'monto_ejecutado' => ['nullable', 'numeric', 'min:0'],
            'notas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'ciclo_campana_id.required' => 'El ciclo de campaña es obligatorio.',
            'categoria.required' => 'La categoría del gasto es obligatoria.',
            'monto_asignado.required' => 'El monto asignado es obligatorio.',
            'monto_asignado.min' => 'El monto asignado debe ser mayor o igual a 0.',
        ];
    }
}
