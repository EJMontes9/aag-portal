<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Seeder;

class InstitutionalPagesSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNosotros();
        $this->seedGuiaViaje();
        $this->seedContacto();
        Page::clearCache();
    }

    private function seedNosotros(): void
    {
        $page = Page::updateOrCreate(
            ['key' => 'nosotros'],
            [
                'title' => 'Nosotros',
                'slug' => 'nosotros',
                'status' => 'published',
                'meta_title' => 'Nosotros · Autoridad Aeroportuaria de Guayaquil',
                'meta_description' => 'Conoce la mision, vision y valores de la Autoridad Aeroportuaria de Guayaquil, administradora del Aeropuerto Internacional Jose Joaquin de Olmedo.',
            ]
        );

        // Reset bloques (idempotente)
        $page->blocks()->delete();

        $blocks = [
            [
                'type' => 'hero',
                'settings' => [
                    'pill' => 'Acerca de la institucion',
                    'pill_tone' => 'soft',
                    'h1' => 'Administramos el aeropuerto *Jose Joaquin de Olmedo* con vision de servicio publico.',
                    'subtitle' => 'Somos la Autoridad Aeroportuaria de Guayaquil, fundacion sin fines de lucro de la M.I. Municipalidad de Guayaquil. Supervisamos la operacion del aeropuerto internacional al servicio de millones de pasajeros cada año.',
                    'cta1_label' => 'Plan estrategico',
                    'cta1_url' => '#plan-estrategico',
                    'cta2_label' => 'Ver transparencia',
                    'cta2_url' => '/transparencia',
                ],
            ],
            [
                'type' => 'values',
                'settings' => [
                    'kicker' => 'NUESTROS PILARES',
                    'title' => 'Cuatro pilares que sostienen nuestra *gestion institucional.*',
                    'subtitle' => 'Cada decision operativa, contractual y ciudadana pasa por estos principios.',
                    'items' => [
                        ['number' => '01', 'title' => 'Eficiencia', 'description' => 'Procesos agiles que respetan el tiempo del viajero y del ciudadano.'],
                        ['number' => '02', 'title' => 'Empatia', 'description' => 'Un aeropuerto hecho para las personas, no para los procedimientos.'],
                        ['number' => '03', 'title' => 'Calidad', 'description' => 'Estandares internacionales aplicados a la operacion diaria.'],
                        ['number' => '04', 'title' => 'Transparencia', 'description' => 'Informacion publica abierta, actualizada y verificable.'],
                    ],
                ],
            ],
            [
                'type' => 'text_image',
                'settings' => [
                    'kicker' => 'PLAN ESTRATEGICO',
                    'title' => 'Hoja de ruta institucional 2024-2028',
                    'body' => "El plan estrategico aprobado por nuestro Directorio define cuatro ejes:\n\n• Modernizacion tecnologica de la infraestructura aeroportuaria\n• Sostenibilidad ambiental y certificacion ACA\n• Experiencia del pasajero y accesibilidad universal\n• Gestion transparente y rendicion de cuentas continua\n\nEstos ejes orientan la inversion y la toma de decisiones operativas de la institucion.",
                    'cta_label' => 'Descargar plan completo (PDF)',
                    'cta_url' => '/transparencia',
                    'image' => null,
                    'layout' => 'image_right',
                    'background' => 'soft',
                ],
            ],
            [
                'type' => 'stats',
                'settings' => [
                    'kicker' => 'EN NUMEROS',
                    'title' => 'La AAG en cifras',
                    'subtitle' => 'Datos representativos de la operacion del Aeropuerto Internacional Jose Joaquin de Olmedo.',
                    'background' => 'navy',
                    'items' => [
                        ['value' => '8.2M', 'label' => 'Pasajeros al ano'],
                        ['value' => '92', 'label' => 'Destinos conectados'],
                        ['value' => '15', 'label' => 'Aerolineas operando'],
                        ['value' => '99.8%', 'label' => 'Disponibilidad operativa'],
                    ],
                ],
            ],
            [
                'type' => 'cta',
                'settings' => [
                    'kicker' => 'TRABAJA CON NOSOTROS',
                    'title' => 'Forma parte del equipo AAG',
                    'subtitle' => 'Revisa nuestras convocatorias vigentes y postula a procesos transparentes de seleccion.',
                    'cta_label' => 'Ver convocatorias',
                    'cta_url' => '/convocatorias',
                    'background' => 'navy',
                ],
            ],
        ];

        $this->createBlocks($page, $blocks);
    }

    private function seedGuiaViaje(): void
    {
        $page = Page::updateOrCreate(
            ['key' => 'guia-de-viaje'],
            [
                'title' => 'Guia de viaje',
                'slug' => 'guia-de-viaje',
                'status' => 'published',
                'meta_title' => 'Guia de viaje · Aeropuerto Jose Joaquin de Olmedo',
                'meta_description' => 'Informacion practica para tu viaje: equipaje, accesos al aeropuerto, transporte, servicios y recomendaciones.',
            ]
        );

        $page->blocks()->delete();

        $blocks = [
            [
                'type' => 'hero',
                'settings' => [
                    'pill' => 'Informacion al viajero',
                    'pill_tone' => 'success',
                    'h1' => 'Tu viaje *empieza* antes de llegar al aeropuerto.',
                    'subtitle' => 'Te acompañamos con la informacion practica que necesitas: equipaje, accesos, transporte, servicios y mas. Un viaje fluido empieza con buena informacion.',
                    'cta1_label' => 'Estado de vuelos',
                    'cta1_url' => '#vuelos',
                    'cta2_label' => 'Como llegar',
                    'cta2_url' => '#como-llegar',
                ],
            ],
            [
                'type' => 'quick_links',
                'settings' => [
                    'kicker' => 'INFORMACION ESENCIAL',
                    'title' => 'Lo que mas consultan nuestros pasajeros',
                    'link_all_label' => 'Ver guia completa →',
                    'link_all_url' => '#guia',
                    'links' => [
                        ['icon' => 'plane', 'label' => 'Estado de vuelos', 'description' => 'Llegadas y salidas en tiempo real', 'url' => '#vuelos'],
                        ['icon' => 'doc', 'label' => 'Equipaje', 'description' => 'Pesos, dimensiones y restricciones', 'url' => '#equipaje'],
                        ['icon' => 'building', 'label' => 'Servicios', 'description' => 'Tiendas, restaurantes y salas VIP', 'url' => '#servicios'],
                        ['icon' => 'globe', 'label' => 'Como llegar', 'description' => 'Accesos, transporte y parqueo', 'url' => '#como-llegar'],
                        ['icon' => 'phone', 'label' => 'Contacto', 'description' => 'Atencion al viajero', 'url' => '/contacto'],
                        ['icon' => 'check', 'label' => 'Recomendaciones', 'description' => 'Tips para un viaje fluido', 'url' => '#recomendaciones'],
                    ],
                ],
            ],
            [
                'type' => 'text_image',
                'settings' => [
                    'kicker' => 'ACCESOS Y TRANSPORTE',
                    'title' => 'Como llegar al aeropuerto',
                    'body' => "El Aeropuerto Internacional Jose Joaquin de Olmedo esta ubicado al norte de la ciudad de Guayaquil, a solo 5 km del centro urbano.\n\nPuedes llegar por:\n\n• Taxi autorizado desde cualquier punto de la ciudad\n• Aerovia (estacion Las Cajas conectada con la terminal)\n• Metrovia (rutas troncales con conexion al aeropuerto)\n• Vehiculo particular con acceso al parqueo de la AAG\n\nRecomendamos llegar con al menos 2 horas de anticipacion para vuelos internacionales y 1 hora para vuelos nacionales.",
                    'cta_label' => null,
                    'cta_url' => null,
                    'layout' => 'image_left',
                    'background' => 'soft',
                ],
            ],
            [
                'type' => 'flights',
                'settings' => [
                    'kicker' => 'EN VIVO',
                    'title' => 'Estado de vuelos',
                    'subtitle' => 'Informacion actualizada de llegadas y salidas.',
                ],
            ],
            [
                'type' => 'cta',
                'settings' => [
                    'kicker' => 'PREGUNTAS FRECUENTES',
                    'title' => '¿Tienes dudas sobre tu viaje?',
                    'subtitle' => 'Revisa la seccion de preguntas frecuentes o contactanos directamente.',
                    'cta_label' => 'Ir a contacto',
                    'cta_url' => '/contacto',
                    'background' => 'soft',
                ],
            ],
        ];

        $this->createBlocks($page, $blocks);
    }

    private function seedContacto(): void
    {
        $page = Page::updateOrCreate(
            ['key' => 'contacto'],
            [
                'title' => 'Contacto',
                'slug' => 'contacto',
                'status' => 'published',
                'meta_title' => 'Contacto · Autoridad Aeroportuaria de Guayaquil',
                'meta_description' => 'Datos de contacto, direccion y canales oficiales de la Autoridad Aeroportuaria de Guayaquil.',
            ]
        );

        $page->blocks()->delete();

        $blocks = [
            [
                'type' => 'hero',
                'settings' => [
                    'pill' => 'Estamos para servirte',
                    'pill_tone' => 'soft',
                    'h1' => 'Conversemos. La *transparencia* empieza con un canal abierto.',
                    'subtitle' => 'Atendemos consultas, sugerencias, quejas y solicitudes de informacion publica. Elige el canal que mejor se ajuste a tu necesidad.',
                    'cta1_label' => 'Llamanos',
                    'cta1_url' => 'tel:+59342169209',
                    'cta2_label' => 'Escribenos',
                    'cta2_url' => 'mailto:info@aag.gob.ec',
                ],
            ],
            [
                'type' => 'quick_links',
                'settings' => [
                    'kicker' => 'CANALES DE ATENCION',
                    'title' => 'Como podemos ayudarte',
                    'link_all_label' => null,
                    'link_all_url' => null,
                    'links' => [
                        ['icon' => 'phone', 'label' => 'Telefono', 'description' => '(593) 4 2169209', 'url' => 'tel:+59342169209'],
                        ['icon' => 'envelope', 'label' => 'Correo institucional', 'description' => 'info@aag.gob.ec', 'url' => 'mailto:info@aag.gob.ec'],
                        ['icon' => 'building', 'label' => 'Direccion', 'description' => 'Av. de las Americas s/n, Guayaquil', 'url' => '#mapa'],
                        ['icon' => 'doc', 'label' => 'Solicitudes LOTAIP', 'description' => 'Solicita informacion publica', 'url' => '/transparencia'],
                        ['icon' => 'user', 'label' => 'Quejas y sugerencias', 'description' => 'Tu opinion mejora el servicio', 'url' => '#formulario'],
                        ['icon' => 'globe', 'label' => 'Redes sociales', 'description' => 'Siguenos para novedades', 'url' => '#redes'],
                    ],
                ],
            ],
            [
                'type' => 'text_image',
                'settings' => [
                    'kicker' => 'NUESTRAS OFICINAS',
                    'title' => 'Visitanos en el aeropuerto',
                    'body' => "Nuestras oficinas administrativas se ubican dentro del complejo del Aeropuerto Internacional Jose Joaquin de Olmedo.\n\n**Direccion**\nAv. de las Americas s/n\nGuayaquil, Ecuador\n\n**Horario de atencion**\nLunes a viernes: 8:00 a 17:00\nSabados: 9:00 a 13:00\nDomingos y feriados: cerrado\n\nPara tramites urgentes o de emergencia operativa, el aeropuerto opera 24/7 a traves de su Centro de Operaciones.",
                    'cta_label' => 'Como llegar',
                    'cta_url' => '/guia-de-viaje#como-llegar',
                    'layout' => 'image_right',
                    'background' => 'bg',
                ],
            ],
            [
                'type' => 'cta',
                'settings' => [
                    'kicker' => 'EMERGENCIAS',
                    'title' => 'Operacion aeroportuaria 24/7',
                    'subtitle' => 'Para situaciones que requieran respuesta inmediata, comunicate con el Centro de Operaciones del aeropuerto.',
                    'cta_label' => 'Llamar al COA',
                    'cta_url' => 'tel:+59342169000',
                    'background' => 'navy',
                ],
            ],
        ];

        $this->createBlocks($page, $blocks);
    }

    private function createBlocks(Page $page, array $blocks): void
    {
        foreach ($blocks as $i => $b) {
            PageBlock::create([
                'page_id' => $page->id,
                'type' => $b['type'],
                'settings' => $b['settings'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }
}
