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
        'alcance_tipo',
        'sesgo_editorial_estimado',
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
