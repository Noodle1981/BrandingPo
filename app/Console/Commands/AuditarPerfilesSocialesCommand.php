<?php

namespace App\Console\Commands;

use App\Models\PerfilSocial;
use App\Services\SocialProfileScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AuditarPerfilesSocialesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:auditar-perfiles-sociales {--perfil_id= : ID específico de perfil a auditar}';

    /**
     * The console command description.
     */
    protected $description = 'Audita diariamente y time-series las métricas públicas de redes sociales (Seguidores, Posts, Likes, Vistas) y calcula los aumentos o pérdidas.';

    /**
     * Execute the console command.
     */
    public function handle(SocialProfileScraperService $scraper): int
    {
        $perfilId = $this->option('perfil_id');

        $query = PerfilSocial::where('esta_activo', true)
            ->whereNotNull('url_perfil')
            ->where('url_perfil', '!=', '');

        if ($perfilId) {
            $query->where('id', $perfilId);
        }

        $perfiles = $query->with('candidato')->get();

        $this->info("Iniciando auditoría automatizada de {$perfiles->count()} perfiles sociales activos...");

        $auditados = 0;
        $errores = 0;

        foreach ($perfiles as $perfil) {
            $this->line(" → Auditando [{$perfil->plataforma}] de {$perfil->candidato?->nombre_completo} ({$perfil->handle_usuario})...");

            try {
                $scraped = $scraper->scrapeProfile($perfil->url_perfil, $perfil->plataforma);

                if (! empty($scraped['seguidores']) || ! empty($scraped['publicaciones']) || ! empty($scraped['foto_perfil_url'])) {
                    $metrica = $perfil->registrarMedicion($scraped, 'cron_24h');
                    $deltaStr = ($metrica->crecimiento_seguidores_dia >= 0 ? '+' : '').$metrica->crecimiento_seguidores_dia;
                    $this->info("   ✓ Éxito: {$perfil->seguidores_actuales} seguidores ({$deltaStr} hoy), {$perfil->publicaciones_totales} posts.");
                    $auditados++;
                } else {
                    $this->warn("   ⚠ No se pudieron extraer datos automáticos para {$perfil->url_perfil}.");
                }
            } catch (\Throwable $e) {
                $this->error('   ✗ Error al auditar: '.$e->getMessage());
                Log::error("Error en cron auditar-perfiles para perfil #{$perfil->id}: ".$e->getMessage());
                $errores++;
            }
        }

        $this->info("Auditoría finalizada. Perfiles procesados con éxito: {$auditados}. Errores/Advertencias: {$errores}.");

        return Command::SUCCESS;
    }
}
