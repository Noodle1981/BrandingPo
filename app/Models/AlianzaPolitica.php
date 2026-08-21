<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlianzaPolitica extends Model
{
    use HasFactory;

    protected $table = 'alianzas_politicas';

    protected $fillable = [
        'workspace_id',
        'candidato_id',
        'nombre_figura',
        'cargo_o_rol',
        'tipo_impacto',
        'notas_observacion',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(Candidato::class);
    }
}
