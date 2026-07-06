<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Modernizacion de pistas y plataformas',
                'summary' => 'Repavimentacion y senalizacion de pistas principales para cumplir con estandares OACI vigentes.',
                'description' => '<p>El proyecto contempla la repavimentacion integral de la pista principal del aeropuerto Jose Joaquin de Olmedo, incluyendo:</p><ul><li>Capa de rodadura nueva</li><li>Senalizacion horizontal y vertical</li><li>Iluminacion de borde y eje de pista</li><li>Mejora de drenajes</li></ul><p>La obra se ejecuta en fases nocturnas para no interrumpir la operacion diurna.</p>',
                'status' => 'en_curso',
                'budget' => 'USD 12.5M',
                'start_date' => '2025-09-01',
                'end_date' => '2026-12-31',
                'location' => 'Aeropuerto JJO, Guayaquil',
                'milestones' => [
                    ['date' => '2025-09-15', 'label' => 'Adjudicacion del contrato', 'completed' => true],
                    ['date' => '2025-10-01', 'label' => 'Inicio de obras', 'completed' => true],
                    ['date' => '2026-03-31', 'label' => 'Fase 1: pista principal', 'completed' => true],
                    ['date' => '2026-09-30', 'label' => 'Fase 2: plataformas', 'completed' => false],
                    ['date' => '2026-12-31', 'label' => 'Entrega final', 'completed' => false],
                ],
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Nueva terminal de carga aerea',
                'summary' => 'Construccion de terminal especializada en carga con capacidad para 80.000 toneladas anuales.',
                'description' => '<p>El proyecto de terminal de carga responde al crecimiento sostenido del comercio exterior de la region.</p><p>Incluye areas refrigeradas, controles aduaneros integrados y conexion directa con la red vial.</p>',
                'status' => 'planificado',
                'budget' => 'USD 28.0M',
                'start_date' => '2026-07-01',
                'end_date' => '2028-06-30',
                'location' => 'Sector norte del aeropuerto',
                'milestones' => [
                    ['date' => '2026-03-01', 'label' => 'Estudios de factibilidad', 'completed' => true],
                    ['date' => '2026-06-30', 'label' => 'Licitacion publica', 'completed' => false],
                ],
                'is_published' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Renovacion de salas VIP',
                'summary' => 'Modernizacion de espacios de alta gama para pasajeros premium y diplomaticos.',
                'description' => '<p>Renovacion integral de las salas VIP del aeropuerto con nuevo mobiliario, iluminacion eficiente y servicios mejorados.</p>',
                'status' => 'completado',
                'budget' => 'USD 1.8M',
                'start_date' => '2025-02-01',
                'end_date' => '2025-08-15',
                'location' => 'Terminal de pasajeros, segundo nivel',
                'milestones' => [
                    ['date' => '2025-02-01', 'label' => 'Inicio de obras', 'completed' => true],
                    ['date' => '2025-08-15', 'label' => 'Apertura al publico', 'completed' => true],
                ],
                'is_published' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Sistema solar fotovoltaico',
                'summary' => 'Instalacion de panel solar para cubrir 35% del consumo electrico del aeropuerto.',
                'description' => '<p>Iniciativa de sostenibilidad ambiental alineada con la certificacion ACA. El sistema sera instalado en techos de edificios operativos.</p>',
                'status' => 'en_curso',
                'budget' => 'USD 4.2M',
                'start_date' => '2026-01-15',
                'end_date' => '2026-10-30',
                'location' => 'Edificios operativos del aeropuerto',
                'milestones' => [
                    ['date' => '2026-01-15', 'label' => 'Instalacion fase 1', 'completed' => true],
                    ['date' => '2026-06-30', 'label' => 'Instalacion fase 2', 'completed' => false],
                ],
                'is_published' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Centro de capacitacion AAG',
                'summary' => 'Espacio formativo para personal aeroportuario y servidores publicos.',
                'description' => '<p>Construccion de un centro de capacitacion con aulas, laboratorios y simuladores para fortalecer la formacion del personal.</p>',
                'status' => 'planificado',
                'budget' => 'USD 3.5M',
                'start_date' => null,
                'end_date' => null,
                'location' => 'Edificio administrativo AAG',
                'milestones' => [],
                'is_published' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($projects as $p) {
            Project::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($p['title'])],
                $p
            );
        }
    }
}
