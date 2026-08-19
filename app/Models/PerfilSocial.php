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
        'demografia_interna_propia',
    ];

    protected $casts = [
        'seguidores_actuales' => 'integer',
        'publicaciones_totales' => 'integer',
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
