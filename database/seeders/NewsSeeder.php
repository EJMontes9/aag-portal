<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Institucional', 'slug' => 'institucional', 'color' => '#1E3A8A', 'sort_order' => 1],
            ['name' => 'Operaciones',   'slug' => 'operaciones',   'color' => '#0F766E', 'sort_order' => 2],
            ['name' => 'Comunicados',   'slug' => 'comunicados',   'color' => '#92400E', 'sort_order' => 3],
            ['name' => 'Eventos',       'slug' => 'eventos',       'color' => '#7E22CE', 'sort_order' => 4],
        ])->map(fn ($c) => NewsCategory::updateOrCreate(['slug' => $c['slug']], $c));

        $author = User::where('email', 'admin@aag.gob.ec')->first()?->id;

        $items = [
            [
                'title' => 'AAG presenta su plan operativo anual 2026',
                'category' => 'institucional',
                'excerpt' => 'El Directorio de la Autoridad Aeroportuaria de Guayaquil aprobo el plan operativo para el periodo 2026, con enfoque en modernizacion tecnologica y experiencia del pasajero.',
                'published_at' => now()->subDays(2),
                'featured' => true,
            ],
            [
                'title' => 'Aeropuerto Jose Joaquin de Olmedo recibe certificacion ambiental',
                'category' => 'operaciones',
                'excerpt' => 'Por cuarto ano consecutivo, el aeropuerto mantiene la certificacion Airport Carbon Accreditation que avala las practicas de reduccion de emisiones.',
                'published_at' => now()->subDays(5),
                'featured' => true,
            ],
            [
                'title' => 'Convocatoria abierta: 12 nuevos puestos en areas tecnicas',
                'category' => 'comunicados',
                'excerpt' => 'La AAG abre proceso de seleccion para profesionales en ingenieria aeroportuaria, sistemas y operaciones. Postulaciones hasta el 30 de junio.',
                'published_at' => now()->subDays(8),
                'featured' => true,
            ],
            [
                'title' => 'Foro de aviacion sostenible reunira a expertos en Guayaquil',
                'category' => 'eventos',
                'excerpt' => 'El proximo 15 de julio se realizara el Foro Internacional de Aviacion Sostenible con participacion de OACI, IATA y autoridades latinoamericanas.',
                'published_at' => now()->subDays(12),
                'featured' => false,
            ],
            [
                'title' => 'Resultados del primer trimestre: crecimiento del 8% en pasajeros',
                'category' => 'institucional',
                'excerpt' => 'El aeropuerto cerro el primer trimestre del ano con un crecimiento del 8% en movimiento de pasajeros respecto al mismo periodo de 2025.',
                'published_at' => now()->subDays(18),
                'featured' => false,
            ],
        ];

        foreach ($items as $i) {
            $cat = $categories->firstWhere('slug', $i['category']);
            News::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($i['title'])],
                [
                    'title' => $i['title'],
                    'category_id' => $cat?->id,
                    'author_id' => $author,
                    'excerpt' => $i['excerpt'],
                    'content' => "<p>{$i['excerpt']}</p>\n<p>Contenido detallado de la noticia. Este texto es un placeholder que sera reemplazado con el cuerpo real del articulo desde el panel administrativo.</p>\n<h2>Contexto</h2>\n<p>La Autoridad Aeroportuaria de Guayaquil mantiene su compromiso con la transparencia y la gestion eficiente del aeropuerto Jose Joaquin de Olmedo.</p>",
                    'status' => 'published',
                    'published_at' => $i['published_at'],
                    'featured_on_home' => $i['featured'],
                ]
            );
        }
    }
}
