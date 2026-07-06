<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Reorganiza el menu header en una estructura con dropdowns para evitar
 * exceso de items top-level. Idempotente: cada vez que se ejecuta deja
 * el header en el estado deseado.
 */
class HeaderMenuReorganizeSeeder extends Seeder
{
    public function run(): void
    {
        $menu = Menu::where('location', 'header')->first();
        if (! $menu) {
            $this->command->warn('No existe menu header. Saltando.');
            return;
        }

        DB::transaction(function () use ($menu) {
            // Borrar todos los items del header (vamos a recrearlos)
            MenuItem::where('menu_id', $menu->id)->delete();

            // Top-level
            $nosotros = MenuItem::create([
                'menu_id' => $menu->id,
                'label' => 'Nosotros',
                'url' => '/nosotros',
                'sort_order' => 10,
                'is_active' => true,
            ]);

            $prensa = MenuItem::create([
                'menu_id' => $menu->id,
                'label' => 'Sala de prensa',
                'url' => '/noticias',
                'sort_order' => 20,
                'is_active' => true,
            ]);

            // Servicios (dropdown)
            $servicios = MenuItem::create([
                'menu_id' => $menu->id,
                'label' => 'Servicios',
                'url' => null,
                'sort_order' => 30,
                'is_active' => true,
            ]);
            MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $servicios->id,
                'label' => 'Guia de viaje', 'url' => '/guia-de-viaje',
                'sort_order' => 1, 'is_active' => true,
            ]);
            MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $servicios->id,
                'label' => 'Estado de vuelos', 'url' => '/guia-de-viaje#vuelos',
                'sort_order' => 2, 'is_active' => true,
            ]);
            MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $servicios->id,
                'label' => 'Preguntas frecuentes', 'url' => '/faq',
                'sort_order' => 3, 'is_active' => true,
            ]);

            // Transparencia (dropdown)
            $transparencia = MenuItem::create([
                'menu_id' => $menu->id,
                'label' => 'Transparencia',
                'url' => null,
                'sort_order' => 40,
                'is_active' => true,
            ]);
            MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $transparencia->id,
                'label' => 'LOTAIP', 'url' => '/transparencia',
                'sort_order' => 1, 'is_active' => true,
            ]);
            MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $transparencia->id,
                'label' => 'Rendicion de cuentas', 'url' => '/rendicion-cuentas',
                'sort_order' => 2, 'is_active' => true,
            ]);
            MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $transparencia->id,
                'label' => 'Proyectos y obras', 'url' => '/proyectos',
                'sort_order' => 3, 'is_active' => true,
            ]);

            // Institucion (dropdown)
            $institucion = MenuItem::create([
                'menu_id' => $menu->id,
                'label' => 'Institucion',
                'url' => null,
                'sort_order' => 50,
                'is_active' => true,
            ]);
            MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $institucion->id,
                'label' => 'Convocatorias', 'url' => '/convocatorias',
                'sort_order' => 1, 'is_active' => true,
            ]);
            MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $institucion->id,
                'label' => 'Trabaja con nosotros', 'url' => '/trabaja-con-nosotros',
                'sort_order' => 2, 'is_active' => true,
            ]);
            MenuItem::create([
                'menu_id' => $menu->id, 'parent_id' => $institucion->id,
                'label' => 'Contacto', 'url' => '/contacto',
                'sort_order' => 3, 'is_active' => true,
            ]);
        });
    }
}
