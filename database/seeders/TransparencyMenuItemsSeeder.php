<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class TransparencyMenuItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['label' => 'Transparencia', 'url' => '/transparencia', 'sort_order' => 30],
            ['label' => 'Rendicion de cuentas', 'url' => '/rendicion-cuentas', 'sort_order' => 40],
        ];

        foreach (['header', 'footer'] as $location) {
            $menu = Menu::where('location', $location)->first();
            if (! $menu) continue;

            foreach ($items as $item) {
                MenuItem::updateOrCreate(
                    ['menu_id' => $menu->id, 'url' => $item['url']],
                    array_merge($item, ['menu_id' => $menu->id, 'is_active' => true])
                );
            }
        }
    }
}
