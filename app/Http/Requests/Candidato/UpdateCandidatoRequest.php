<?php

namespace App\Http\Requests\Candidato;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCandidatoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        $workspace = WorkspaceHelper::activo($this);

        return [
            'workspace_nombre' => ['nullable', 'string', 'max:255'],
            'nombre_completo' => ['required', 'string', 'max:255'],
            'partido_coalicion' => ['required', 'string', 'max:255'],
            'cargo_aspirado' => ['nullable', 'string', 'max:255'],
            'estado_politico' => ['required', 'string'],
            'ciclo_campana_id' => ['nullable', Rule::exists('ciclo_campanas', 'id')->where('workspace_id', $workspace->id)],
            'territorio_id' => ['nullable'],
            'territorio_nombre' => ['nullable', 'string', 'max:255'],
            'padron_electoral' => ['nullable', 'integer', 'min:0'],
            'poblacion_total' => ['nullable', 'integer', 'min:0'],
            'tipo_territorio' => ['nullable', 'string', 'max:50'],
            'color_hex' => ['nullable', 'string', 'max:10'],
            'avatar_url' => ['nullable', 'url', 'max:500'],
            'bio_resumen' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'partido_coalicion.required' => 'El partido o coalición es obligatorio.',
            'estado_politico.required' => 'El estado político es obligatorio.',
        ];
    }
}
