<?php

namespace App\Http\Requests\Medios;

use App\Helpers\SecurityHelper;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canWrite() ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo_medio' => ['required', 'in:digital,impreso,radio,tv'],
            'url_sitio' => ['nullable', 'url', 'max:500'],
            'url_facebook' => ['nullable', 'url', 'max:500'],
            'avatar_url' => ['nullable', 'string', 'max:1000'],
            'feed_rss_url' => ['nullable', 'url', 'max:500'],
            'alcance_tipo' => ['required', 'in:local,provincial,nacional'],
            'sesgo_editorial_estimado' => ['required', 'in:oficialista,independiente,opositor,neutral'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $urlSitio = $this->input('url_sitio');
            if (! empty($urlSitio) && ! SecurityHelper::esUrlSegura($urlSitio)) {
                $v->errors()->add('url_sitio', 'La URL del portal web no es válida o apunta a un host restringido.');
            }

            $urlFb = $this->input('url_facebook');
            if (! empty($urlFb) && ! SecurityHelper::esUrlSegura($urlFb)) {
                $v->errors()->add('url_facebook', 'La URL de Facebook no es válida o apunta a un host restringido.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del medio es obligatorio.',
            'tipo_medio.required' => 'El tipo de medio es obligatorio.',
            'alcance_tipo.required' => 'El alcance territorial es obligatorio.',
            'sesgo_editorial_estimado.required' => 'El sesgo editorial es obligatorio.',
        ];
    }
}
