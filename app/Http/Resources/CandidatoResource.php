<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidatoResource extends JsonResource
{
    /**
     * Transforma el recurso de Candidato en un array seguro para Inertia y API.
     * Soporta tanto modelos Eloquent como colecciones mapeadas.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $r = is_array($this->resource) ? (object) $this->resource : $this->resource;

        $perfiles = null;
        if (isset($r->perfilesSociales) && $r->perfilesSociales) {
            $perfiles = collect($r->perfilesSociales)->map(function ($p) {
                $pObj = is_array($p) ? (object) $p : $p;

                return [
                    'id' => $pObj->id ?? null,
                    'plataforma' => $pObj->plataforma ?? $pObj->red_social ?? '',
                    'red_social' => $pObj->red_social ?? $pObj->plataforma ?? '',
                    'handle' => $pObj->handle ?? $pObj->handle_usuario ?? '',
                    'handle_usuario' => $pObj->handle_usuario ?? $pObj->handle ?? '',
                    'url_perfil' => $pObj->url_perfil ?? null,
                    'seguidores' => $pObj->seguidores_actuales ?? 0,
                    'seguidores_actuales' => $pObj->seguidores_actuales ?? 0,
                    'esta_verificado' => (bool) ($pObj->esta_verificado ?? false),
                    'esta_activo' => (bool) ($pObj->esta_activo ?? true),
                    'color_estado' => $pObj->color_estado ?? 'gris',
                ];
            })->all();
        }

        return [
            'id' => $r->id ?? null,
            'nombre_completo' => $r->nombre_completo ?? '',
            'apodo' => $r->apodo ?? null,
            'partido_coalicion' => $r->partido_coalicion ?? '',
            'cargo_al_que_postula' => $r->cargo_al_que_postula ?? $r->cargo_aspirado ?? null,
            'cargo_aspirado' => $r->cargo_aspirado ?? $r->cargo_al_que_postula ?? null,
            'estado_politico' => $r->estado_politico ?? 'candidato',
            'color_hex' => $r->color_hex ?? $r->color_identidad ?? '#06b6d4',
            'color_identidad' => $r->color_identidad ?? $r->color_hex ?? '#06b6d4',
            'es_propio' => (bool) ($r->es_propio ?? false),
            'foto_url' => $r->foto_url ?? $r->avatar_url ?? null,
            'avatar_url' => $r->avatar_url ?? $r->foto_url ?? null,
            'bio_resumen' => $r->bio_resumen ?? null,
            'activo' => (bool) ($r->activo ?? true),
            'territorio_id' => $r->territorio_id ?? null,
            'ciclo_campana_id' => $r->ciclo_campana_id ?? null,
            'perfiles_sociales' => $perfiles,
            'created_at' => isset($r->created_at)
                ? ($r->created_at instanceof Carbon ? $r->created_at->toISOString() : (string) $r->created_at)
                : null,
            'updated_at' => isset($r->updated_at)
                ? ($r->updated_at instanceof Carbon ? $r->updated_at->toISOString() : (string) $r->updated_at)
                : null,
        ];
    }
}
