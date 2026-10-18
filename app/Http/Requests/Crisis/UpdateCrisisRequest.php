<?php

namespace App\Http\Requests\Crisis;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCrisisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', 'in:abierto,en_contencion,resuelto'],
            'estrategia_contencion' => ['nullable', 'string'],
            'minutos_tiempo_respuesta' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El estado de la crisis es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
