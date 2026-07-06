<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class VerifyContactSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::where('slug', 'contacto')->with('activeBlocks')->first();

        if (! $page) {
            $this->command->error('No existe página con slug "contacto".');
            return;
        }

        $block = $page->activeBlocks->first();

        $this->command->info("Página    : {$page->title}");
        $this->command->info("Estado    : {$page->status}");
        $this->command->info("Bloques   : {$page->activeBlocks->count()}");
        $this->command->info("Tipo      : " . ($block->type ?? 'ninguno'));
        $this->command->info("form_id   : " . ($block->settings['form_id'] ?? 'ninguno'));
    }
}
