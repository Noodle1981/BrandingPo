<?php

namespace App\Http\Requests\Candidato;

use Illuminate\Foundation\Http\FormRequest;

class ScrapePerfilSocialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url'],
            'plataforma' => ['required', 'string'],
        ];
    }
}
