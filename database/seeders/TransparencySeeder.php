<?php

namespace Database\Seeders;

use App\Models\LotaipMonth;
use App\Models\LotaipYear;
use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Database\Seeder;

class TransparencySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedYearsAndMonths();
        $this->seedPage(
            key: 'transparencia',
            slug: 'transparencia',
            title: 'Transparencia',
            section: 'lotaip',
            metaTitle: 'Transparencia · Autoridad Aeroportuaria de Guayaquil',
            metaDescription: 'Informacion publica de la AAG en cumplimiento de la LOTAIP. Documentos por año y mes.',
            kicker: 'TRANSPARENCIA',
            title2: 'Informacion publica · LOTAIP',
            intro: "La Ley Organica de Transparencia y Acceso a la Informacion Publica (LOTAIP) plantea la participacion ciudadana y el derecho de acceso a la informacion relacionada con asuntos publicos.\n\nEn cumplimiento de la LOTAIP, todas las entidades poseedoras de informacion publica estan obligadas a difundir, a traves de su portal institucional, la informacion minima actualizada como lo dispone el Art. 7 de la LOTAIP."
        );

        $this->seedPage(
            key: 'rendicion-cuentas',
            slug: 'rendicion-cuentas',
            title: 'Rendicion de cuentas',
            section: 'rendicion',
            metaTitle: 'Rendicion de cuentas · Autoridad Aeroportuaria de Guayaquil',
            metaDescription: 'Informes de rendicion de cuentas anuales de la AAG.',
            kicker: 'TRANSPARENCIA',
            title2: 'Rendicion de cuentas',
            intro: "En cumplimiento del marco normativo de participacion ciudadana, la AAG presenta anualmente su informe de rendicion de cuentas para la sociedad civil y los organos de control."
        );

        Page::clearCache();
    }

    private function seedYearsAndMonths(): void
    {
        // Crea años 2023-2026 para LOTAIP con meses Enero-Diciembre activos
        // Solo si no existen (idempotente)
        foreach (['lotaip', 'rendicion'] as $section) {
            foreach ([2023, 2024, 2025, 2026] as $year) {
                $y = LotaipYear::firstOrCreate(
                    ['section' => $section, 'year' => $year],
                    ['is_active' => true, 'sort_order' => 0]
                );
                for ($m = 1; $m <= 12; $m++) {
                    LotaipMonth::firstOrCreate(
                        ['year_id' => $y->id, 'month' => $m],
                        ['mode' => 'files', 'is_active' => true]
                    );
                }
            }
        }
    }

    private function seedPage(
        string $key, string $slug, string $title, string $section,
        string $metaTitle, string $metaDescription,
        string $kicker, string $title2, string $intro
    ): void {
        $page = Page::updateOrCreate(
            ['key' => $key],
            [
                'title' => $title,
                'slug' => $slug,
                'status' => 'published',
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
            ]
        );

        $page->blocks()->delete();

        PageBlock::create([
            'page_id' => $page->id,
            'type' => 'transparency_browser',
            'settings' => [
                'section' => $section,
                'kicker' => $kicker,
                'title' => $title2,
                'intro' => $intro,
            ],
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }
}
