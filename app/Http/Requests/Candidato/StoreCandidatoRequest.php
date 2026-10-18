<?php

namespace App\Http\Requests\Candidato;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCandidatoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        $workspace = WorkspaceHelper::activo($this);

        return [
            'nombre_completo' => ['required', 'string', 'max:255'],
            'partido_coalicion' => ['required', 'string', 'max:255'],
            'cargo_aspirado' => ['nullable', 'string', 'max:255'],
            'estado_politico' => ['required', 'string'],
            'ciclo_campana_id' => ['required', Rule::exists('ciclo_campanas', 'id')->where('workspace_id', $workspace->id)],
            'territorio_id' => ['nullable', Rule::exists('territorios', 'id')->where('workspace_id', $workspace->id)],
            'color_hex' => ['nullable', 'string', 'max:10'],
            'avatar_url' => ['nullable', 'url', 'max:500'],
            'bio_resumen' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_completo.required' => 'El nombre completo del candidato es obligatorio.',
            'partido_coalicion.required' => 'El partido o coalición es obligatorio.',
            'estado_politico.required' => 'El estado político es obligatorio.',
            'ciclo_campana_id.required' => 'El ciclo de campaña es obligatorio.',
            'ciclo_campana_id.exists' => 'El ciclo de campaña seleccionado no pertenece a este workspace.',
        ];
    }
}
