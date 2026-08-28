<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EjeTematico extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'pilar_principal',
        'nombre',
        'slug',
        'color_badge',
        'icono',
        'orden',
        'descripcion',
    ];

    /**
     * Workspace al que pertenece este registro.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
