<?php

namespace App\Http\Requests\Publicacion;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarFechaPublicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        return [
            'fecha_publicacion' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_publicacion.required' => 'La fecha de publicación es obligatoria.',
            'fecha_publicacion.date' => 'La fecha de publicación debe ser una fecha válida.',
        ];
    }
}
