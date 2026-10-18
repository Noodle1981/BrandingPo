<?php

namespace App\Http\Requests\Territorio;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTerritorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'in:municipio,departamento,provincia,seccion'],
            'parent_id' => ['nullable', 'exists:territorios,id'],
            'codigo_indec' => ['nullable', 'string', 'max:50'],
            'poblacion_total' => ['nullable', 'integer', 'min:0'],
            'padron_electoral' => ['nullable', 'integer', 'min:0'],
            'poblacion_urbana_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'poblacion_rural_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hogares_nbi_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del territorio es obligatorio.',
            'tipo.required' => 'El tipo de territorio es obligatorio.',
        ];
    }
}
