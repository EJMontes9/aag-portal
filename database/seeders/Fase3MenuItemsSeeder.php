<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class Fase3MenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        // Header: agregar FAQ y Proyectos si no existen
        $header = Menu::where('location', 'header')->first();
        if ($header) {
            $items = [
                ['label' => 'Proyectos y obras', 'url' => '/proyectos', 'sort_order' => 50],
                ['label' => 'Preguntas frecuentes', 'url' => '/faq', 'sort_order' => 60],
            ];
            foreach ($items as $item) {
                MenuItem::updateOrCreate(
                    ['menu_id' => $header->id, 'url' => $item['url']],
                    array_merge($item, ['menu_id' => $header->id, 'is_active' => true])
                );
            }
        }

        // Footer: agregar FAQ y Proyectos
        $footer = Menu::where('location', 'footer')->first();
        if ($footer) {
            $items = [
                ['label' => 'Proyectos y obras', 'url' => '/proyectos', 'sort_order' => 50],
                ['label' => 'Preguntas frecuentes', 'url' => '/faq', 'sort_order' => 60],
            ];
            foreach ($items as $item) {
                MenuItem::updateOrCreate(
                    ['menu_id' => $footer->id, 'url' => $item['url']],
                    array_merge($item, ['menu_id' => $footer->id, 'is_active' => true])
                );
            }
        }
    }
}
