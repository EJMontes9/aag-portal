<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Seeder;

class AddMapBlockToContactSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::where('slug', 'contacto')->first();

        if (! $page) {
            $this->command->error('No se encontró la página con slug "contacto".');
            return;
        }

        // Evitar duplicados
        $exists = $page->blocks()->where('type', 'map')->exists();
        if ($exists) {
            $this->command->warn('La página "contacto" ya tiene un bloque de tipo "map". No se creó uno nuevo.');
            return;
        }

        $maxOrder = $page->blocks()->max('sort_order') ?? 0;

        $page->blocks()->create([
            'type'       => 'map',
            'sort_order' => $maxOrder + 1,
            'is_active'  => true,
            'settings'   => [
                'title'      => 'Cómo llegar',
                'embed_code' => '',   // Admin debe pegar el iframe de Google Maps
                'height'     => 'medium',
                'background' => 'soft',
            ],
        ]);

        $this->command->info('✓ Bloque "Mapa interactivo" agregado a la página de contacto (sort_order: ' . ($maxOrder + 1) . ').');
        $this->command->line('  Recuerda pegar el código iframe de Google Maps desde el Editor Avanzado o el Editor Visual.');
    }
}
