<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilSocial extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidato_id',
        'plataforma',
        'handle_usuario',
        'url_perfil',
        'seguidores_actuales',
        'publicaciones_totales',
        'esta_verificado',
        'esta_activo',
        'seguidos_actuales',
        'foto_perfil_url',
        'fecha_punto_cero',
        'seguidores_punto_cero',
        'seguidos_punto_cero',
        'publicaciones_punto_cero',
        'notas_punto_cero',
        'demografia_interna_propia',
    ];

    protected $casts = [
        'seguidores_actuales' => 'integer',
        'publicaciones_totales' => 'integer',
        'seguidos_actuales' => 'integer',
        'esta_verificado' => 'boolean',
        'esta_activo' => 'boolean',
        'fecha_punto_cero' => 'date',
        'seguidores_punto_cero' => 'integer',
        'seguidos_punto_cero' => 'integer',
        'publicaciones_punto_cero' => 'integer',
        'demografia_interna_propia' => 'array',
    ];

    /**
     * Candidato propietario de esta red social.
     */
    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }
}
