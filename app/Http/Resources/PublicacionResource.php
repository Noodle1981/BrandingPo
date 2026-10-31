<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicacionResource extends JsonResource
{
    /**
     * Transforma la Publicación en un array seguro para Inertia y API.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'candidato_id' => $this->candidato_id,
            'perfil_social_id' => $this->perfil_social_id,
            'plataforma' => $this->plataforma,
            'post_id_red' => $this->post_id_red,
            'url_publicacion' => $this->url_publicacion,
            'texto' => $this->texto,
            'fecha_publicacion' => $this->fecha_publicacion?->toISOString() ?? (string) $this->fecha_publicacion,
            'tipo_formato' => $this->tipo_formato,
            'imagen_url' => $this->imagen_url,
            'video_url' => $this->video_url,
            'es_pauta' => (bool) $this->es_pauta,
            'origen_pauta' => $this->origen_pauta,
            'monto_pauta_ars' => (float) ($this->monto_pauta_ars ?? 0),
            'monto_invertido_ars' => (float) ($this->monto_invertido_ars ?? $this->monto_pauta_ars ?? 0),
            'alcance_estimado' => (int) ($this->alcance_estimado ?? 0),
            'impresiones' => (int) ($this->impresiones ?? 0),
            'interacciones' => [
                'likes' => (int) ($this->likes_count ?? 0),
                'comments' => (int) ($this->comments_count ?? 0),
                'shares' => (int) ($this->shares_count ?? 0),
                'reposts' => (int) ($this->reposts_count ?? 0),
                'saves' => (int) ($this->saves_count ?? 0),
                'views' => (int) ($this->views_count ?? 0),
                'total' => (int) ($this->interacciones_totales ?? 0),
            ],
            'sentimiento' => [
                'predominante' => $this->sentimiento_predominante ?? 'neutral',
                'indice_aprobacion' => (float) ($this->indice_aprobacion_neta ?? 0),
                'desglose_emojis' => $this->desglose_emojis ?? [],
            ],
            'ejes' => $this->whenLoaded('ejesEstrategicos', function () {
                return $this->ejesEstrategicos->map(fn ($e) => [
                    'id' => $e->id,
                    'nombre' => $e->nombre,
                    'color_hex' => $e->color_hex ?? '#06b6d4',
                ]);
            }),
            'candidato' => $this->whenLoaded('candidato', function () {
                return [
                    'id' => $this->candidato->id,
                    'nombre_completo' => $this->candidato->nombre_completo,
                    'es_propio' => (bool) $this->candidato->es_propio,
                    'foto_url' => $this->candidato->foto_url,
                ];
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
