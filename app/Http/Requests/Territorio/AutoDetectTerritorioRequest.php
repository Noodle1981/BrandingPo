<?php

namespace App\Http\Requests\Territorio;

use Illuminate\Foundation\Http\FormRequest;

class AutoDetectTerritorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:255'],
        ];
    }
}
