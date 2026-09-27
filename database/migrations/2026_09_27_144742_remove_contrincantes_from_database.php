<?php

use App\Models\Candidato;
use App\Models\PerfilSocial;
use App\Models\PerfilSocialMetrica;
use App\Models\Publicacion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener IDs de candidatos rivales/oposición
        $rivalesIds = Candidato::where('es_propio', false)->pluck('id');

        if ($rivalesIds->isNotEmpty()) {
            // Perfiles de rivales
            $perfilesIds = PerfilSocial::whereIn('candidato_id', $rivalesIds)->pluck('id');

            // Eliminar métricas y publicaciones de perfiles rivales
            PerfilSocialMetrica::whereIn('perfil_social_id', $perfilesIds)->delete();
            Publicacion::whereIn('candidato_id', $rivalesIds)->delete();
            PerfilSocial::whereIn('id', $perfilesIds)->delete();

            // Eliminar los candidatos rivales
            Candidato::whereIn('id', $rivalesIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversible
    }
};
