<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Territorio extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'parent_id',
        'nombre',
        'tipo',
        'codigo_indec',
        'latitud',
        'longitud',
        'poblacion_total',
        'padron_electoral',
        'poblacion_urbana_pct',
        'poblacion_rural_pct',
        'hogares_nbi_pct',
        'piramide_etaria',
        'circuitos_electorales',
        'meta_electoral',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'poblacion_total' => 'integer',
        'padron_electoral' => 'integer',
        'poblacion_urbana_pct' => 'decimal:2',
        'poblacion_rural_pct' => 'decimal:2',
        'hogares_nbi_pct' => 'decimal:2',
        'latitud' => 'float',
        'longitud' => 'float',
        'piramide_etaria' => 'array',
        'circuitos_electorales' => 'array',
        'meta_electoral' => 'array',
    ];

    /**
     * Workspace al que pertenece este registro.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Territorio Padre (ej. Provincia respecto a Departamentos/Municipios).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Territorio::class, 'parent_id');
    }

    /**
     * Departamentos / Municipios que pertenecen a esta Provincia.
     */
    public function departamentos(): HasMany
    {
        return $this->hasMany(Territorio::class, 'parent_id');
    }

    /**
     * Candidatos vinculados a este territorio.
     */
    public function candidatos(): HasMany
    {
        return $this->hasMany(Candidato::class);
    }

    /**
     * Candidato propio oficial de campaña en este territorio.
     */
    public function candidatoPropio(): HasOne
    {
        return $this->hasOne(Candidato::class)->where('es_propio', true);
    }

    /**
     * Scope para filtrar sólo Provincias.
     */
    public function scopeProvincias(Builder $query): Builder
    {
        return $query->where('tipo', 'provincia')->orWhereNull('parent_id');
    }

    /**
     * Scope para filtrar sólo Municipios / Departamentos.
     */
    public function scopeMunicipios(Builder $query): Builder
    {
        return $query->whereIn('tipo', ['municipio', 'departamento']);
    }
}
