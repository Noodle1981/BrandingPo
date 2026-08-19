<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicacionHistorico extends Model
{
    use HasFactory;

    protected $table = 'publicacion_historicos';

    protected $fillable = [
        'publicacion_id',
        'fecha_corte',
        'vistas_corte',
        'likes_corte',
        'comentarios_corte',
        'compartidos_corte',
    ];

    protected $casts = [
        'fecha_corte' => 'datetime',
        'vistas_corte' => 'integer',
        'likes_corte' => 'integer',
        'comentarios_corte' => 'integer',
        'compartidos_corte' => 'integer',
    ];

    /**
     * Publicación a la que pertenece este corte histórico.
     */
    public function publicacion(): BelongsTo
    {
        return $this->belongsTo(Publicacion::class);
    }
}
