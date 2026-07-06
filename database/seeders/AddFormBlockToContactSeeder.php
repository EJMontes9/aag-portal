<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Seeder;

class AddFormBlockToContactSeeder extends Seeder
{
    public function run(): void
    {
        $form = \App\Models\Form::where('slug', 'contacto')->first();

        if (! $form) {
            $this->command->error('No se encontró el formulario con slug "contacto". Corre ContactFormSeeder primero.');
            return;
        }

        $page = Page::where('slug', 'contacto')->first();

        if (! $page) {
            $this->command->error('No se encontró la página con slug "contacto".');
            return;
        }

        // Verificar que no exista ya un bloque tipo 'form'
        $yaExiste = $page->blocks()->where('type', 'form')->exists();

        if ($yaExiste) {
            $this->command->info('La página ya tiene un bloque de formulario.');
            return;
        }

        // Ponerlo al final
        $maxOrder = (int) $page->blocks()->max('sort_order');

        PageBlock::create([
            'page_id'    => $page->id,
            'type'       => 'form',
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
            'settings'   => [
                'form_id'             => $form->id,
                'section_title'       => 'Escríbenos',
                'section_description' => 'Completa el formulario y te responderemos en menos de 24 horas hábiles.',
                'layout'              => 'centered',
            ],
        ]);

        // Limpiar caché de la página
        Page::clearCache($page->key ?: $page->slug);

        $this->command->info("Bloque de formulario agregado a la página '{$page->title}' (form_id: {$form->id}).");
    }
}
