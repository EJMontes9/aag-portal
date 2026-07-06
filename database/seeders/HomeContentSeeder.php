<?php

namespace Database\Seeders;

use App\Models\Convocatoria;
use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        // Paginas
        $home = Page::updateOrCreate(
            ['key' => 'home'],
            ['title' => 'Home', 'slug' => 'home', 'status' => 'published']
        );

        // Convocatoria ejemplo (si no existe)
        Convocatoria::updateOrCreate(
            ['slug' => 'jefe-operaciones-aeroportuarias'],
            [
                'title' => 'Jefe de Operaciones Aeroportuarias',
                'area' => 'Operaciones',
                'modality' => 'Presencial · Guayaquil',
                'short_description' => 'Liderar las operaciones del Aeropuerto Internacional Jose Joaquin de Olmedo.',
                'requirements' => [
                    'Titulo de tercer nivel en Ingenieria, Administracion o afines',
                    'Minimo 5 años de experiencia en operaciones aeroportuarias',
                    'Conocimiento de normativa OACI y regulaciones DGAC',
                    'Ingles intermedio-avanzado (B2 o superior)',
                ],
                'closes_at' => now()->addDays(8)->setTime(17, 0),
                'status' => 'vigente',
                'alert_mode' => 'none',
                'alert_frequency' => 'session',
                'featured_on_home' => true,
            ]
        );

        // Limpia bloques existentes para sembrar desde cero
        $home->blocks()->delete();

        // --- Bloque 1: HERO ---
        $home->blocks()->create([
            'type' => 'hero',
            'sort_order' => 0,
            'is_active' => true,
            'settings' => [
                'pill' => 'Aeropuerto operando con normalidad',
                'pill_tone' => 'success',
                'h1' => 'Conectar *Guayaquil* con el mundo, con *claridad.*',
                'subtitle' => 'La Autoridad Aeroportuaria de Guayaquil administra y supervisa la operacion del Aeropuerto Internacional Jose Joaquin de Olmedo, garantizando un servicio seguro, transparente y cercano a la ciudadania.',
                'cta1_label' => 'Ver estado de vuelos',
                'cta1_url' => '#vuelos',
                'cta2_label' => 'Portal de transparencia',
                'cta2_url' => '/transparencia',
                'stats' => [
                    ['value' => '8.2M', 'label' => 'Pasajeros al año'],
                    ['value' => '22', 'label' => 'Destinos directos'],
                    ['value' => '14', 'label' => 'Aerolineas activas'],
                    ['value' => '97%', 'label' => 'Puntualidad 2025'],
                ],
                'cards' => [
                    [
                        'variant' => 'image',
                        'kicker' => 'DESTACADO',
                        'title' => 'Nuevas rutas 2026 desde el JJO',
                        'cta_label' => 'Conoce los destinos',
                        'cta_url' => '/noticias',
                    ],
                    [
                        'variant' => 'primary',
                        'kicker' => 'CONVOCATORIA',
                        'title' => 'Abierta la postulacion para el cargo de Jefe de Operaciones',
                        'meta' => 'Hasta el 28 de abril',
                        'cta_label' => 'Ver detalles',
                        'cta_url' => '/convocatorias',
                    ],
                    [
                        'variant' => 'surface',
                        'kicker' => 'GUIA DE VIAJE',
                        'title' => 'Todo lo que necesitas saber antes de volar',
                        'cta_label' => 'Leer guia',
                        'cta_url' => '/guia-viaje',
                    ],
                ],
            ],
        ]);

        // --- Bloque 2: Accesos ---
        $home->blocks()->create([
            'type' => 'quick_links',
            'sort_order' => 1,
            'is_active' => true,
            'settings' => [
                'kicker' => 'ACCESOS DIRECTOS',
                'title' => 'Lo que mas se consulta',
                'link_all_label' => 'Ver todos los servicios →',
                'link_all_url' => '/servicios',
                'links' => [
                    ['icon' => 'plane', 'label' => 'Vuelos en tiempo real', 'description' => 'Llegadas y salidas actualizadas', 'url' => '#vuelos'],
                    ['icon' => 'doc', 'label' => 'Transparencia LOTAIP', 'description' => 'Informacion publica por mes', 'url' => '/transparencia'],
                    ['icon' => 'check', 'label' => 'Convocatorias', 'description' => 'Postula a una vacante', 'url' => '/convocatorias'],
                    ['icon' => 'building', 'label' => 'Guia del pasajero', 'description' => 'Servicios en la terminal', 'url' => '/guia-viaje'],
                    ['icon' => 'download', 'label' => 'Rendicion de cuentas', 'description' => 'Informes anuales', 'url' => '/transparencia/rendicion'],
                    ['icon' => 'phone', 'label' => 'Atencion ciudadana', 'description' => 'Canales de contacto', 'url' => '/contacto'],
                ],
            ],
        ]);

        // --- Bloque 3: Vuelos ---
        $home->blocks()->create([
            'type' => 'flights',
            'sort_order' => 2,
            'is_active' => true,
            'settings' => [
                'kicker' => 'ESTADO DE VUELOS',
                'title' => 'Consulta llegadas y salidas en tiempo real.',
                'subtitle' => 'Accede al sistema oficial de informacion de vuelos del Aeropuerto Jose Joaquin de Olmedo. Horarios, puertas y estado operativo actualizados al minuto.',
                'cta_label' => 'Ir al portal de vuelos',
                'cta_url' => 'https://tagsa.aero/vuelos',
                'cta_note' => 'Se abre en una nueva pestaña · Sitio del operador',
            ],
        ]);

        // --- Bloque 4: Convocatoria ---
        $home->blocks()->create([
            'type' => 'convocatoria',
            'sort_order' => 3,
            'is_active' => true,
            'settings' => [
                'convocatoria_id' => null,
            ],
        ]);

        // --- Bloque 5: Valores ---
        $home->blocks()->create([
            'type' => 'values',
            'sort_order' => 4,
            'is_active' => true,
            'settings' => [
                'kicker' => 'NUESTROS VALORES',
                'title' => 'Un aeropuerto *cercano*, una gestion *institucional.*',
                'subtitle' => 'Trabajamos para que cada pasajero que pisa el Jose Joaquin de Olmedo se sienta en casa, y para que cada ciudadano pueda verificar como se administra lo publico.',
                'items' => [
                    ['number' => '01', 'title' => 'Eficiencia', 'description' => 'Procesos agiles que respetan el tiempo del viajero y del ciudadano.'],
                    ['number' => '02', 'title' => 'Empatia', 'description' => 'Un aeropuerto hecho para las personas, no para los procedimientos.'],
                    ['number' => '03', 'title' => 'Calidad', 'description' => 'Estandares internacionales aplicados a la operacion diaria.'],
                    ['number' => '04', 'title' => 'Transparencia', 'description' => 'Informacion publica abierta, actualizada y verificable.'],
                ],
            ],
        ]);

        Cache::flush();
    }
}
