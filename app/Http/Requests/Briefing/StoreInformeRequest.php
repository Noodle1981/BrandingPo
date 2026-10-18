<?php

namespace App\Http\Requests\Briefing;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInformeRequest extends FormRequest
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
            'titulo' => ['required', 'string', 'max:255'],
            'periodo_cubierto' => ['required', 'string', 'max:255'],
            'resumen_ejecutivo' => ['required', 'string'],
            'conclusiones_estrategicas' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'ciclo_campana_id.required' => 'El ciclo de campaña es obligatorio.',
            'titulo.required' => 'El título del informe es obligatorio.',
            'periodo_cubierto.required' => 'El período cubierto es obligatorio.',
            'resumen_ejecutivo.required' => 'El resumen ejecutivo es obligatorio.',
        ];
    }
}
