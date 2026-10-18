<?php

namespace App\Http\Requests\Crisis;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlianzaRequest extends FormRequest
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
            'nombre_figura' => ['required', 'string', 'max:255'],
            'cargo_o_rol' => ['required', 'string', 'max:255'],
            'tipo_impacto' => ['required', 'in:suma,resta,neutro'],
            'notas_observacion' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'candidato_id.required' => 'El candidato es obligatorio.',
            'nombre_figura.required' => 'El nombre de la figura es obligatorio.',
            'cargo_o_rol.required' => 'El cargo o rol es obligatorio.',
            'tipo_impacto.required' => 'El tipo de impacto político es obligatorio.',
        ];
    }
}
