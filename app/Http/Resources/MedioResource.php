<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedioResource extends JsonResource
{
    /**
     * Transforma el recurso de Medio de Prensa en un array seguro y completo para Inertia.
     * Soporta tanto modelos Eloquent como colecciones mapeadas.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $r = is_array($this->resource) ? (object) $this->resource : $this->resource;

        return [
            'id' => $r->id ?? null,
            'nombre' => $r->nombre ?? '',
            'tipo_medio' => $r->tipo_medio ?? 'web',
            'alcance_tipo' => $r->alcance_tipo ?? 'provincial',
            'alcance_estimado' => (int) ($r->alcance_estimado ?? 0),
            'sesgo_editorial_estimado' => $r->sesgo_editorial_estimado ?? 'neutral',
            'url_sitio' => $r->url_sitio ?? null,
            'url_facebook' => $r->url_facebook ?? null,
            'url_rss_feed' => $r->url_rss_feed ?? $r->feed_rss_url ?? null,
            'feed_rss_url' => $r->feed_rss_url ?? $r->url_rss_feed ?? null,
            'avatar_url' => $r->avatar_url ?? null,
            'activo' => (bool) ($r->activo ?? true),
            'notas_prensa_count' => (int) ($r->notas_prensa_count ?? $r->notas_count ?? 0),
            'notas_web_count' => (int) ($r->notas_web_count ?? 0),
            'notas_fb_count' => (int) ($r->notas_fb_count ?? 0),
            'notas_filtradas_count' => (int) ($r->notas_filtradas_count ?? 0),
            'ultima_sincronizacion_at' => isset($r->ultima_sincronizacion_at)
                ? ($r->ultima_sincronizacion_at instanceof Carbon ? $r->ultima_sincronizacion_at->toISOString() : (string) $r->ultima_sincronizacion_at)
                : null,
            'created_at' => isset($r->created_at)
                ? ($r->created_at instanceof Carbon ? $r->created_at->toISOString() : (string) $r->created_at)
                : null,
            'updated_at' => isset($r->updated_at)
                ? ($r->updated_at instanceof Carbon ? $r->updated_at->toISOString() : (string) $r->updated_at)
                : null,
        ];
    }
}
