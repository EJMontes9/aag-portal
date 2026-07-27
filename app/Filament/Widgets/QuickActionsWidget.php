<?php

namespace App\Filament\Widgets;

use App\Models\Page;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    // Los accesos rapidos son de contenido general (editar el home, crear
    // pagina...). El rol "transparencia" no gestiona nada de eso: sin este
    // filtro veia el mismo panel que un editor, con enlaces a secciones a las
    // que ademas no tiene permiso de entrar.
    public static function canView(): bool
    {
        return auth()->user()?->can('widget_QuickActionsWidget') ?? false;
    }

    protected function getViewData(): array
    {
        $homePage = Page::where('key', 'home')->first();

        return [
            'homePageId' => $homePage?->id,
            'userName' => auth()->user()?->name ?? 'Administrador',
        ];
    }
}
