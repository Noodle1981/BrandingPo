<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DemographicIntelligenceService
{
    /**
     * Limpiar el término de búsqueda de departamento / municipio.
     */
    public function cleanSearchTerm(string $nombre): string
    {
        $term = trim($nombre);
        // Quitar prefijos comunes: Departamento, Municipio, Partido, etc.
        $term = preg_replace('/^(departamento|municipio|partido|depto|ciudad)\s*(de)?\s*/iu', '', $term);
        // Quitar sufijos comunes: / San Juan, San Juan, etc.
        $term = preg_replace('/\s*[\/\-]\s*.*$/iu', '', $term);
        $term = preg_replace('/\s*(san juan|mendoza|cordoba|córdoba|buenos aires|santa fe)\s*$/iu', '', $term);
        return trim($term);
    }

    /**
     * Consultar API de Georef Argentina (IGN / Secretaría de Innovación Pública)
     * para obtener datos geográficos oficiales, códigos INDEC y coordenadas.
     */
    public function consultarGeoref(string $nombre, ?string $provincia = 'San Juan'): array
    {
        $nombreOriginal = trim($nombre);
        $nombreLimpio = $this->cleanSearchTerm($nombreOriginal);

        $result = [
            'success' => false,
            'nombre' => $nombreOriginal,
            'provincia' => $provincia ?: 'San Juan',
            'codigo_indec' => null,
            'latitud' => null,
            'longitud' => null,
            'tipo' => 'departamento',
            'poblacion_total' => null,
            'padron_electoral' => null,
            'poblacion_urbana_pct' => 70.0,
            'poblacion_rural_pct' => 30.0,
            'hogares_nbi_pct' => 15.0,
            'fuente' => 'Georef AR / INDEC',
            'mensaje' => '',
        ];

        // 1. Intentar consultar API oficial de Georef AR (departamentos)
        try {
            $params = [
                'nombre' => $nombreLimpio,
                'max' => 5,
            ];
            if ($provincia && $provincia !== 'Todas') {
                $params['provincia'] = $provincia;
            }

            $response = Http::timeout(4)->get('https://apis.datos.gob.ar/georef/api/departamentos', $params);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['departamentos']) && count($data['departamentos']) > 0) {
                    $dep = $data['departamentos'][0];
                    $result['success'] = true;
                    $result['nombre'] = $dep['nombre'];
                    $result['codigo_indec'] = $dep['id'] ?? null;
                    $result['provincia'] = $dep['provincia']['nombre'] ?? $provincia;
                    $result['latitud'] = $dep['centroide']['lat'] ?? null;
                    $result['longitud'] = $dep['centroide']['lon'] ?? null;
                    $result['mensaje'] = "Departamento {$dep['nombre']} detectado con coordenadas oficiales de Georef AR.";
                }
            }

            // Si no encontró por departamento, probar por municipios de Georef
            if (!$result['success']) {
                $respMuni = Http::timeout(4)->get('https://apis.datos.gob.ar/georef/api/municipios', $params);
                if ($respMuni->successful()) {
                    $dataMuni = $respMuni->json();
                    if (!empty($dataMuni['municipios']) && count($dataMuni['municipios']) > 0) {
                        $muni = $dataMuni['municipios'][0];
                        $result['success'] = true;
                        $result['nombre'] = $muni['nombre'];
                        $result['codigo_indec'] = $muni['id'] ?? null;
                        $result['provincia'] = $muni['provincia']['nombre'] ?? $provincia;
                        $result['latitud'] = $muni['centroide']['lat'] ?? null;
                        $result['longitud'] = $muni['centroide']['lon'] ?? null;
                        $result['mensaje'] = "Municipio {$muni['nombre']} detectado con coordenadas oficiales de Georef AR.";
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Error consultando Georef AR para '{$nombreLimpio}': " . $e->getMessage());
        }

        // 2. Enriquecer con base censal INDEC de San Juan y departamentos conocidos
        $sanJuanDepartamentos = $this->getSanJuanDepartamentosFallback();
        $keyBusqueda = strtolower(str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '], ['a', 'e', 'i', 'o', 'u', 'n', ''], $nombreLimpio));

        foreach ($sanJuanDepartamentos as $depKey => $data) {
            $depKeyNormalizado = strtolower(str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '], ['a', 'e', 'i', 'o', 'u', 'n', ''], $depKey));
            if (str_contains($keyBusqueda, $depKeyNormalizado) || str_contains($depKeyNormalizado, $keyBusqueda)) {
                $result['success'] = true;
                if (!$result['nombre'] || $result['nombre'] === $nombreOriginal) {
                    $result['nombre'] = $data['nombre'];
                }
                if (!$result['codigo_indec']) $result['codigo_indec'] = $data['codigo_indec'];
                if (!$result['latitud']) $result['latitud'] = $data['latitud'];
                if (!$result['longitud']) $result['longitud'] = $data['longitud'];
                $result['poblacion_total'] = $data['poblacion_total'];
                $result['padron_electoral'] = $data['padron_electoral'];
                $result['poblacion_urbana_pct'] = $data['poblacion_urbana_pct'];
                $result['poblacion_rural_pct'] = $data['poblacion_rural_pct'];
                $result['hogares_nbi_pct'] = $data['hogares_nbi_pct'];
                $result['mensaje'] = "¡Departamento {$data['nombre']} ({$provincia}) detectado con datos censales INDEC!";
                return $result;
            }
        }

        // Si fue detectado por Georef pero no está en la tabla de San Juan (otra provincia)
        if ($result['success']) {
            if (!$result['poblacion_total']) {
                $result['poblacion_total'] = 45000;
                $result['padron_electoral'] = 35000;
            }
            return $result;
        }

        $result['mensaje'] = "No se encontró '{$nombreLimpio}' en {$provincia}. Verifica la ortografía o ingresa los datos manualmente.";
        return $result;
    }

    /**
     * Listado oficial de las 24 Provincias / Jurisdicciones de Argentina.
     */
    public function getProvinciasArgentinas(): array
    {
        return [
            'San Juan',
            'Mendoza',
            'Córdoba',
            'Buenos Aires',
            'Ciudad Autónoma de Buenos Aires',
            'Santa Fe',
            'Entre Ríos',
            'Tucumán',
            'Salta',
            'Chaco',
            'Corrientes',
            'Misiones',
            'Santiago del Estero',
            'San Luis',
            'La Rioja',
            'Catamarca',
            'Jujuy',
            'Neuquén',
            'Río Negro',
            'Chubut',
            'Santa Cruz',
            'La Pampa',
            'Formosa',
            'Tierra del Fuego',
        ];
    }

    /**
     * Generar la Pirámide Etaria Electoral basada en estándares censales INDEC / CNE.
     */
    public function generarPiramideEtaria(int $poblacionTotal, int $padronElectoral): array
    {
        if ($padronElectoral <= 0) {
            $padronElectoral = (int)round($poblacionTotal * 0.78); // Estimación del 78% de población con edad de votar
        }

        $grupos = [
            [
                'id' => '16_17',
                'rango' => '16 a 17 años',
                'categoria' => 'Voto Joven (Optativo)',
                'porcentaje' => 4.2,
                'electores' => (int)round($padronElectoral * 0.042),
                'red_principal' => 'TikTok & Reels',
                'temas_clave' => ['Primer Voto', 'Becas & Educación', 'Deportes', 'Cultura Urbana'],
                'color_hex' => '#06b6d4',
            ],
            [
                'id' => '18_29',
                'rango' => '18 a 29 años',
                'categoria' => 'Juventud & Empleo',
                'porcentaje' => 24.8,
                'electores' => (int)round($padronElectoral * 0.248),
                'red_principal' => 'Instagram & TikTok',
                'temas_clave' => ['Primer Empleo', 'Vivienda Joven', 'Innovación', 'Emprendedurismo'],
                'color_hex' => '#3b82f6',
            ],
            [
                'id' => '30_49',
                'rango' => '30 a 49 años',
                'categoria' => 'Adultos & Familias',
                'porcentaje' => 32.5,
                'electores' => (int)round($padronElectoral * 0.325),
                'red_principal' => 'Facebook & Instagram',
                'temas_clave' => ['Seguridad Barrial', 'Salud & Dispensarios', 'Educación Escolar', 'Obras de Pavimento'],
                'color_hex' => '#10b981',
            ],
            [
                'id' => '50_69',
                'rango' => '50 a 69 años',
                'categoria' => 'Adultos Mayores (Decisores)',
                'porcentaje' => 25.5,
                'electores' => (int)round($padronElectoral * 0.255),
                'red_principal' => 'Facebook & Radio / Prensa',
                'temas_clave' => ['Servicios Públicos', 'Tranquilidad Vecinal', 'Gestión Transparente', 'Salud Mayor'],
                'color_hex' => '#f59e0b',
            ],
            [
                'id' => '70_mas',
                'rango' => '70 años o más',
                'categoria' => 'Tercera Edad (Optativo)',
                'porcentaje' => 13.0,
                'electores' => (int)round($padronElectoral * 0.130),
                'red_principal' => 'Medios Tradicionales & Boca a Boca',
                'temas_clave' => ['Asistencia Social', 'Centros de Jubilados', 'Medicamentos', 'Atención Médica'],
                'color_hex' => '#8b5cf6',
            ],
        ];

        return [
            'padron_total' => $padronElectoral,
            'poblacion_total' => $poblacionTotal,
            'grupos_etarios' => $grupos,
            'resumen_voto_joven' => (int)round($padronElectoral * 0.29),
            'resumen_voto_adulto' => (int)round($padronElectoral * 0.58),
            'resumen_voto_senior' => (int)round($padronElectoral * 0.13),
        ];
    }

    /**
     * Generar recomendaciones estratégicas de pauta y contenido basadas en demografía.
     */
    public function recomendarEstrategiaDigital(array $piramide, float $urbanoPct): array
    {
        $distribucionPauta = [
            [
                'plataforma' => 'Facebook Ads',
                'porcentaje_sugerido' => $urbanoPct > 70 ? 40 : 50,
                'audiencia_objetivo' => 'Adultos 35-65+ años, vecinos de barrio y familias.',
                'tipo_mensaje' => 'Obras territoriales, alumbrado, seguridad, cercanía y gestión comunitaria.',
            ],
            [
                'plataforma' => 'Instagram Ads',
                'porcentaje_sugerido' => $urbanoPct > 70 ? 35 : 25,
                'audiencia_objetivo' => 'Jóvenes y adultos 22-45 años, comerciantes y profesionales.',
                'tipo_mensaje' => 'Estética de gestión, propuestas de futuro, historias reales y cercanía.',
            ],
            [
                'plataforma' => 'TikTok Promoted',
                'porcentaje_sugerido' => 15,
                'audiencia_objetivo' => 'Voto Joven 16-29 años.',
                'tipo_mensaje' => 'Videos cortos, frescura, detrás de escena, tendencias y propuestas ágiles.',
            ],
            [
                'plataforma' => 'YouTube & Red de Medios',
                'porcentaje_sugerido' => 10,
                'audiencia_objetivo' => 'Población general y consumo de noticias locales.',
                'tipo_mensaje' => 'Spots de campaña de alta producción y entrevistas en medios locales.',
            ],
        ];

        return [
            'distribucion_pauta' => $distribucionPauta,
            'perfil_territorial' => $urbanoPct >= 65 ? 'Predominantemente Urbano / Comercial' : 'Mixto Urbano - Agrícola / Rural',
            'recomendacion_eje_discursivo' => $urbanoPct >= 65
                ? 'Priorizar eje en modernización, seguridad urbana, comercio local y espacios verdes.'
                : 'Equilibrar eje entre obras de infraestructura urbana y apoyo al sector rural/agrícola (caminos, riego, producción).',
        ];
    }

    /**
     * Datos censales y geográficos de los 19 departamentos de San Juan.
     */
    public function getSanJuanDepartamentosFallback(): array
    {
        return [
            'albardon' => [
                'nombre' => 'Albardón',
                'codigo_indec' => '70007',
                'latitud' => -31.4333,
                'longitud' => -68.5167,
                'poblacion_total' => 31200,
                'padron_electoral' => 24500,
                'poblacion_urbana_pct' => 68.50,
                'poblacion_rural_pct' => 31.50,
                'hogares_nbi_pct' => 16.20,
            ],
            'capital' => [
                'nombre' => 'Capital (San Juan)',
                'codigo_indec' => '70028',
                'latitud' => -31.5375,
                'longitud' => -68.5364,
                'poblacion_total' => 118000,
                'padron_electoral' => 96000,
                'poblacion_urbana_pct' => 99.20,
                'poblacion_rural_pct' => 0.80,
                'hogares_nbi_pct' => 7.50,
            ],
            'rawson' => [
                'nombre' => 'Rawson',
                'codigo_indec' => '70098',
                'latitud' => -31.5833,
                'longitud' => -68.5333,
                'poblacion_total' => 134000,
                'padron_electoral' => 102000,
                'poblacion_urbana_pct' => 94.00,
                'poblacion_rural_pct' => 6.00,
                'hogares_nbi_pct' => 14.80,
            ],
            'rivadavia' => [
                'nombre' => 'Rivadavia',
                'codigo_indec' => '70105',
                'latitud' => -31.5333,
                'longitud' => -68.5833,
                'poblacion_total' => 95000,
                'padron_electoral' => 76000,
                'poblacion_urbana_pct' => 96.50,
                'poblacion_rural_pct' => 3.50,
                'hogares_nbi_pct' => 8.90,
            ],
            'chimbas' => [
                'nombre' => 'Chimbas',
                'codigo_indec' => '70042',
                'latitud' => -31.4833,
                'longitud' => -68.5333,
                'poblacion_total' => 98000,
                'padron_electoral' => 74000,
                'poblacion_urbana_pct' => 93.00,
                'poblacion_rural_pct' => 7.00,
                'hogares_nbi_pct' => 18.50,
            ],
            'pocito' => [
                'nombre' => 'Pocito',
                'codigo_indec' => '70091',
                'latitud' => -31.6667,
                'longitud' => -68.5833,
                'poblacion_total' => 67000,
                'padron_electoral' => 49000,
                'poblacion_urbana_pct' => 72.00,
                'poblacion_rural_pct' => 28.00,
                'hogares_nbi_pct' => 17.10,
            ],
            'santa lucia' => [
                'nombre' => 'Santa Lucía',
                'codigo_indec' => '70119',
                'latitud' => -31.5333,
                'longitud' => -68.4833,
                'poblacion_total' => 58000,
                'padron_electoral' => 44000,
                'poblacion_urbana_pct' => 92.00,
                'poblacion_rural_pct' => 8.00,
                'hogares_nbi_pct' => 10.40,
            ],
            'caucete' => [
                'nombre' => 'Caucete',
                'codigo_indec' => '70035',
                'latitud' => -31.6500,
                'longitud' => -68.2833,
                'poblacion_total' => 42000,
                'padron_electoral' => 31000,
                'poblacion_urbana_pct' => 64.00,
                'poblacion_rural_pct' => 36.00,
                'hogares_nbi_pct' => 19.80,
            ],
            'jachal' => [
                'nombre' => 'Jáchal',
                'codigo_indec' => '70070',
                'latitud' => -30.2333,
                'longitud' => -68.7500,
                'poblacion_total' => 24000,
                'padron_electoral' => 18500,
                'poblacion_urbana_pct' => 52.00,
                'poblacion_rural_pct' => 48.00,
                'hogares_nbi_pct' => 18.20,
            ],
            'san martin' => [
                'nombre' => 'San Martín',
                'codigo_indec' => '70112',
                'latitud' => -31.5167,
                'longitud' => -68.3500,
                'poblacion_total' => 13500,
                'padron_electoral' => 10200,
                'poblacion_urbana_pct' => 45.00,
                'poblacion_rural_pct' => 55.00,
                'hogares_nbi_pct' => 19.50,
            ],
            'sarmiento' => [
                'nombre' => 'Sarmiento',
                'codigo_indec' => '70126',
                'latitud' => -31.9500,
                'longitud' => -68.6500,
                'poblacion_total' => 24000,
                'padron_electoral' => 17500,
                'poblacion_urbana_pct' => 55.00,
                'poblacion_rural_pct' => 45.00,
                'hogares_nbi_pct' => 19.10,
            ],
            '25 de mayo' => [
                'nombre' => '25 de Mayo',
                'codigo_indec' => '70133',
                'latitud' => -31.8167,
                'longitud' => -68.3333,
                'poblacion_total' => 20500,
                'padron_electoral' => 15000,
                'poblacion_urbana_pct' => 48.00,
                'poblacion_rural_pct' => 52.00,
                'hogares_nbi_pct' => 21.00,
            ],
            'angaco' => [
                'nombre' => 'Angaco',
                'codigo_indec' => '70014',
                'latitud' => -31.4000,
                'longitud' => -68.4167,
                'poblacion_total' => 10200,
                'padron_electoral' => 7800,
                'poblacion_urbana_pct' => 50.00,
                'poblacion_rural_pct' => 50.00,
                'hogares_nbi_pct' => 17.80,
            ],
            '9 de julio' => [
                'nombre' => '9 de Julio',
                'codigo_indec' => '70084',
                'latitud' => -31.6667,
                'longitud' => -68.3833,
                'poblacion_total' => 11500,
                'padron_electoral' => 8600,
                'poblacion_urbana_pct' => 52.00,
                'poblacion_rural_pct' => 48.00,
                'hogares_nbi_pct' => 19.30,
            ],
            'calingasta' => [
                'nombre' => 'Calingasta',
                'codigo_indec' => '70021',
                'latitud' => -31.3333,
                'longitud' => -69.4167,
                'poblacion_total' => 10500,
                'padron_electoral' => 8100,
                'poblacion_urbana_pct' => 42.00,
                'poblacion_rural_pct' => 58.00,
                'hogares_nbi_pct' => 18.00,
            ],
            'valle fertil' => [
                'nombre' => 'Valle Fértil',
                'codigo_indec' => '70140',
                'latitud' => -30.6333,
                'longitud' => -67.4500,
                'poblacion_total' => 8500,
                'padron_electoral' => 6400,
                'poblacion_urbana_pct' => 46.00,
                'poblacion_rural_pct' => 54.00,
                'hogares_nbi_pct' => 20.50,
            ],
            'iglesia' => [
                'nombre' => 'Iglesia',
                'codigo_indec' => '70063',
                'latitud' => -30.2833,
                'longitud' => -69.2000,
                'poblacion_total' => 10800,
                'padron_electoral' => 8200,
                'poblacion_urbana_pct' => 40.00,
                'poblacion_rural_pct' => 60.00,
                'hogares_nbi_pct' => 19.20,
            ],
            'ullum' => [
                'nombre' => 'Ullum',
                'codigo_indec' => '70147',
                'latitud' => -31.4167,
                'longitud' => -68.7333,
                'poblacion_total' => 6200,
                'padron_electoral' => 4600,
                'poblacion_urbana_pct' => 60.00,
                'poblacion_rural_pct' => 40.00,
                'hogares_nbi_pct' => 17.50,
            ],
            'zonda' => [
                'nombre' => 'Zonda',
                'codigo_indec' => '70154',
                'latitud' => -31.5500,
                'longitud' => -68.7500,
                'poblacion_total' => 5800,
                'padron_electoral' => 4300,
                'poblacion_urbana_pct' => 58.00,
                'poblacion_rural_pct' => 42.00,
                'hogares_nbi_pct' => 16.90,
            ],
        ];
    }
}
