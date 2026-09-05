<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicacionPautaEvento extends Model
{
    use HasFactory;

    protected $table = 'publicacion_pauta_eventos';

    protected $fillable = [
        'publicacion_id',
        'tipo_pauta_anterior',
        'tipo_pauta_nuevo',
        'monto_anterior',
        'monto_nuevo',
        'fecha_evento',
        'seguidores_canal_snapshot',
        'likes_snapshot',
        'comentarios_snapshot',
        'compartidos_snapshot',
        'vistas_snapshot',
        'republicados_snapshot',
        'registrado_por',
        'origen',
        'notas',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
        'monto_anterior' => 'decimal:2',
        'monto_nuevo' => 'decimal:2',
        'seguidores_canal_snapshot' => 'integer',
        'likes_snapshot' => 'integer',
        'comentarios_snapshot' => 'integer',
        'compartidos_snapshot' => 'integer',
        'vistas_snapshot' => 'integer',
        'republicados_snapshot' => 'integer',
    ];

    protected $appends = [
        'delta_likes_atribuibles',
        'delta_comentarios_atribuibles',
        'delta_vistas_atribuibles',
        'costo_por_like',
    ];

    /**
     * Publicación asociada a este evento de pauta.
     */
    public function publicacion(): BelongsTo
    {
        return $this->belongsTo(Publicacion::class);
    }

    /**
     * Usuario que registró el evento (o null si fue automático por auto_sync).
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Likes generados desde el snapshot de corte hasta la métrica actual del post.
     */
    public function getDeltaLikesAtribuiblesAttribute(): int
    {
        $pub = $this->relationLoaded('publicacion') ? $this->publicacion : $this->publicacion()->first();
        if (! $pub) {
            return 0;
        }

        return max(0, (int) $pub->total_likes - (int) $this->likes_snapshot);
    }

    /**
     * Comentarios generados desde el corte.
     */
    public function getDeltaComentariosAtribuiblesAttribute(): int
    {
        $pub = $this->relationLoaded('publicacion') ? $this->publicacion : $this->publicacion()->first();
        if (! $pub) {
            return 0;
        }

        return max(0, (int) $pub->total_comentarios - (int) $this->comentarios_snapshot);
    }

    /**
     * Vistas generadas desde el corte.
     */
    public function getDeltaVistasAtribuiblesAttribute(): int
    {
        $pub = $this->relationLoaded('publicacion') ? $this->publicacion : $this->publicacion()->first();
        if (! $pub) {
            return 0;
        }

        return max(0, (int) $pub->total_vistas - (int) $this->vistas_snapshot);
    }

    /**
     * Costo por Like (CPL) atribuible a la inversión del evento.
     */
    public function getCostoPorLikeAttribute(): ?float
    {
        $monto = (float) $this->monto_nuevo;
        $delta = $this->delta_likes_atribuibles;

        if ($monto <= 0 || $delta <= 0) {
            return null;
        }

        return round($monto / $delta, 2);
    }
}
