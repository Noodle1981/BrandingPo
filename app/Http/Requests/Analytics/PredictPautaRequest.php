<?php

namespace App\Http\Requests\Analytics;

use App\Helpers\WorkspaceHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PredictPautaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workspace = WorkspaceHelper::activo($this);

        return [
            'monto' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'formato' => ['nullable', 'string', 'max:100'],
            'plataforma' => ['nullable', 'string', 'max:50'],
            'candidato_id' => ['nullable', Rule::exists('candidatos', 'id')->where('workspace_id', $workspace->id)],
        ];
    }
}
