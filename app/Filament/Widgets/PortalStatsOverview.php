<?php

namespace App\Filament\Widgets;

use App\Models\Convocatoria;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PortalStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    // Paginas, convocatorias y usuarios del panel: cifras de contenido
    // general, ajenas al rol "transparencia".
    public static function canView(): bool
    {
        return auth()->user()?->can('widget_PortalStatsOverview') ?? false;
    }

    protected function getStats(): array
    {
        $pagesCount = Page::count();
        $blocksCount = PageBlock::count();
        $convVigentes = Convocatoria::vigentes()->count();
        $convCerradas = Convocatoria::count() - $convVigentes;
        $usersCount = User::count();

        return [
            Stat::make('Paginas publicadas', $pagesCount)
                ->description($blocksCount . ' bloques en total')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('primary')
                ->icon('heroicon-o-document-text'),

            Stat::make('Convocatorias vigentes', $convVigentes)
                ->description($convCerradas . ' cerradas/archivadas')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color($convVigentes > 0 ? 'success' : 'gray')
                ->icon('heroicon-o-megaphone'),

            Stat::make('Usuarios del panel', $usersCount)
                ->description('Equipo con acceso administrativo')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('info')
                ->icon('heroicon-o-users'),
        ];
    }
}
