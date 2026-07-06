<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Crea el menu 'topbar' con los 3 enlaces secundarios del header azul:
 * Preguntas Frecuentes, Contactanos, Portal del Empleado.
 * Editables desde /admin/menus.
 */
class TopbarMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = Menu::updateOrCreate(
            ['location' => 'topbar'],
            [
                'name' => 'Topbar (barra superior)',
                'slug' => 'topbar',
                'description' => 'Enlaces rapidos de la barra azul superior. Idealmente 2-4 items.',
                'is_active' => true,
            ]
        );

        $items = [
            ['label' => 'Preguntas frecuentes', 'url' => '/faq', 'sort_order' => 10],
            ['label' => 'Contactanos', 'url' => '/contacto', 'sort_order' => 20],
            ['label' => 'Portal del empleado', 'url' => '/trabaja-con-nosotros', 'sort_order' => 30],
        ];

        foreach ($items as $item) {
            MenuItem::updateOrCreate(
                ['menu_id' => $menu->id, 'url' => $item['url']],
                array_merge($item, ['menu_id' => $menu->id, 'is_active' => true])
            );
        }
    }
}
