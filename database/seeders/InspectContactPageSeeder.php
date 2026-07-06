<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class InspectContactPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::where('slug', 'contacto')->with('blocks')->first();

        foreach ($page->blocks as $block) {
            $this->command->info("── Bloque: {$block->type} | orden: {$block->sort_order} | activo: " . ($block->is_active ? 'sí' : 'no'));
            $s = $block->settings ?? [];

            // Buscar cualquier clave relacionada con mapa
            foreach ($s as $key => $val) {
                if (str_contains(strtolower($key), 'map') || str_contains(strtolower((string)$val), 'iframe') || str_contains(strtolower((string)$val), 'google')) {
                    $this->command->line("   {$key}: " . substr((string)$val, 0, 120));
                }
            }

            // Si es text-image o similar, mostrar todas las keys
            if (in_array($block->type, ['text-image', 'cta', 'stats'])) {
                $this->command->line("   keys: " . implode(', ', array_keys($s)));
            }
        }
    }
}
