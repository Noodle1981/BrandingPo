<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerritorioResource extends JsonResource
{
    /**
     * Transforma el recurso de Territorio en un array seguro.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'tipo' => $this->tipo,
            'parent_id' => $this->parent_id,
            'codigo_indec' => $this->codigo_indec,
            'poblacion_total' => (int) ($this->poblacion_total ?? 0),
            'padron_electoral' => (int) ($this->padron_electoral ?? 0),
            'poblacion_urbana_pct' => (float) ($this->poblacion_urbana_pct ?? 0),
            'poblacion_rural_pct' => (float) ($this->poblacion_rural_pct ?? 0),
            'hogares_nbi_pct' => (float) ($this->hogares_nbi_pct ?? 0),
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
