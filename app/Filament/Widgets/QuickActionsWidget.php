<?php

namespace App\Filament\Widgets;

use App\Models\Page;
use Filament\Widgets\Widget;

class QuickActionsWidget extends Widget
{
    protected static string $view = 'filament.widgets.quick-actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getViewData(): array
    {
        $homePage = Page::where('key', 'home')->first();

        return [
            'homePageId' => $homePage?->id,
            'userName' => auth()->user()?->name ?? 'Administrador',
        ];
    }
}
