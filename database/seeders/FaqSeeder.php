<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $cats = collect([
            ['name' => 'Viajes y vuelos',       'slug' => 'viajes',       'sort_order' => 1],
            ['name' => 'Tramites institucionales','slug' => 'tramites',   'sort_order' => 2],
            ['name' => 'Servicios del aeropuerto','slug' => 'servicios',  'sort_order' => 3],
            ['name' => 'Transparencia',         'slug' => 'transparencia','sort_order' => 4],
        ])->map(fn ($c) => FaqCategory::updateOrCreate(['slug' => $c['slug']], $c));

        $faqs = [
            ['Viajes y vuelos', 'viajes', 'Con cuanto tiempo de anticipacion debo llegar al aeropuerto?',
             '<p>Para <strong>vuelos internacionales</strong> recomendamos llegar con <strong>3 horas</strong> de anticipacion.</p><p>Para <strong>vuelos nacionales</strong>, con <strong>1.5 horas</strong> es suficiente.</p>', true, 1],
            ['Viajes y vuelos', 'viajes', 'Donde puedo consultar el estado de mi vuelo?',
             '<p>Puedes consultar el estado de tu vuelo en la seccion <a href="/guia-de-viaje#vuelos">Estado de vuelos</a> de nuestra guia, o directamente con tu aerolinea.</p>', true, 2],
            ['Viajes y vuelos', 'viajes', 'Cuales son los limites de equipaje?',
             '<p>Los limites de equipaje los establece cada aerolinea. En general:</p><ul><li><strong>Equipaje de mano:</strong> 1 pieza de hasta 8-10kg</li><li><strong>Equipaje facturado:</strong> 1-2 piezas de hasta 23kg cada una</li></ul><p>Verifica con tu aerolinea antes de viajar.</p>', false, 3],

            ['Tramites institucionales', 'tramites', 'Como solicito informacion publica a la AAG?',
             '<p>Puedes presentar tu solicitud de informacion publica (LOTAIP) a traves de la <a href="/transparencia">seccion de transparencia</a> o directamente en nuestras oficinas.</p><p>El plazo de respuesta es de <strong>10 dias habiles</strong>.</p>', true, 1],
            ['Tramites institucionales', 'tramites', 'Donde reviso las convocatorias laborales vigentes?',
             '<p>Todas las convocatorias activas estan publicadas en <a href="/convocatorias">/convocatorias</a>. Tambien recibiras alertas si has ingresado a nuestro boletin.</p>', true, 2],
            ['Tramites institucionales', 'tramites', 'Como presento una queja o sugerencia?',
             '<p>Puedes hacerlo a traves del formulario en <a href="/contacto">/contacto</a> o llamando a nuestras lineas de atencion.</p>', false, 3],

            ['Servicios del aeropuerto', 'servicios', 'Hay parqueo en el aeropuerto?',
             '<p>Si, el aeropuerto cuenta con parqueo de corta y larga estadia. Las tarifas y disponibilidad pueden consultarse en el sitio del operador del aeropuerto.</p>', false, 1],
            ['Servicios del aeropuerto', 'servicios', 'Hay servicio de internet WiFi gratis?',
             '<p>Si, el aeropuerto ofrece WiFi gratuito en todas las salas de espera y areas publicas. Conectate a la red <strong>"GYE-Airport"</strong> y acepta los terminos de uso.</p>', false, 2],

            ['Transparencia', 'transparencia', 'Donde encuentro el plan estrategico de la AAG?',
             '<p>El plan estrategico vigente esta disponible para descarga en la <a href="/nosotros">seccion Nosotros</a> y en el <a href="/transparencia">portal de transparencia</a>.</p>', true, 1],
            ['Transparencia', 'transparencia', 'Como verifico la ejecucion presupuestaria?',
             '<p>Publicamos trimestralmente los informes de ejecucion presupuestaria en la seccion de Transparencia conforme a la LOTAIP.</p>', false, 2],
        ];

        foreach ($faqs as [$catName, $catSlug, $question, $answer, $featured, $order]) {
            $cat = $cats->firstWhere('slug', $catSlug);
            Faq::updateOrCreate(
                ['question' => $question],
                [
                    'answer' => $answer,
                    'category_id' => $cat?->id,
                    'is_active' => true,
                    'featured' => $featured,
                    'sort_order' => $order,
                ]
            );
        }
    }
}
