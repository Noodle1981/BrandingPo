<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedioPrensa extends Model
{
    use HasFactory;

    protected $table = 'medios_prensa';

    protected $fillable = [
        'workspace_id',
        'territorio_id',
        'nombre',
        'tipo_medio',
        'url_sitio',
        'url_facebook',
        'avatar_url',
        'feed_rss_url',
        'configuracion_scraping',
        'ultima_sincronizacion_at',
        'alcance_tipo',
        'sesgo_editorial_estimado',
    ];

    protected $casts = [
        'configuracion_scraping' => 'array',
        'ultima_sincronizacion_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function territorio(): BelongsTo
    {
        return $this->belongsTo(Territorio::class);
    }

    public function notasPrensa(): HasMany
    {
        return $this->hasMany(NotaPrensa::class);
    }
}
