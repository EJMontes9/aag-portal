<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Seeder;

class ContactPageSeeder extends Seeder
{
    public function run(): void
    {
        $form = \App\Models\Form::where('slug', 'contacto')->first();

        if (! $form) {
            $this->command->error('Primero corre ContactFormSeeder.');
            return;
        }

        $page = Page::firstOrCreate(
            ['slug' => 'contacto'],
            [
                'key'              => 'contacto',
                'title'            => 'Contáctanos',
                'status'           => 'published',
                'meta_title'       => 'Contáctanos — AAG Portal',
                'meta_description' => 'Escríbenos y te responderemos en menos de 24 horas hábiles.',
            ]
        );

        if ($page->blocks()->count() === 0) {
            PageBlock::create([
                'page_id'    => $page->id,
                'type'       => 'form',
                'is_active'  => true,
                'sort_order' => 1,
                'settings'   => [
                    'form_id'             => $form->id,
                    'section_title'       => 'Contáctanos',
                    'section_description' => 'Escríbenos y te responderemos en menos de 24 horas hábiles.',
                    'layout'              => 'centered',
                ],
            ]);
            $this->command->info("Página 'Contáctanos' creada en /contacto con el formulario.");
        } else {
            $this->command->info("La página ya existía con bloques, no se modificó.");
        }
    }
}
