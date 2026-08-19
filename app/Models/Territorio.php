<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Territorio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
        'poblacion_total',
        'padron_electoral',
    ];

    protected $casts = [
        'poblacion_total' => 'integer',
        'padron_electoral' => 'integer',
    ];

    /**
     * Candidatos vinculados a este territorio.
     */
    public function candidatos(): HasMany
    {
        return $this->hasMany(Candidato::class);
    }
}
