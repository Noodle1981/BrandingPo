<?php

namespace App\Http\Requests\Medios;

use Illuminate\Foundation\Http\FormRequest;

class DetectarFuentesMedioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url_sitio' => ['nullable', 'url', 'max:500'],
            'url_facebook' => ['nullable', 'url', 'max:500'],
        ];
    }
}
