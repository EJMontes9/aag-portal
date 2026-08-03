<?php

namespace App\Filament\Widgets;

use App\Models\Convocatoria;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentConvocatoriasWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Convocatorias recientes';

    // El rol "transparencia" no administra convocatorias: no tiene sentido
    // que este listado ocupe su panel de entrada.
    public static function canView(): bool
    {
        return auth()->user()?->can('widget_RecentConvocatoriasWidget') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Convocatoria::query()
                    ->latest('updated_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->wrap()
                    ->limit(60)
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('area')
                    ->label('Área')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('effective_status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vigente' => 'success',
                        'cerrada' => 'gray',
                        'archivada' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('closes_at')
                    ->label('Cierra')
                    ->dateTime('d M Y · H:i')
                    ->placeholder('—')
                    ->description(fn (Convocatoria $r): ?string => $r->closes_at && $r->closes_at->isPast()
                        ? 'Cerrada hace '.$r->closes_at->diffForHumans(null, true)
                        : ($r->closes_at ? 'Cierra '.$r->closes_at->diffForHumans() : null)),

                Tables\Columns\IconColumn::make('featured_on_home')
                    ->label('Home')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Editar')
                    ->icon('heroicon-m-pencil-square')
                    ->size('sm')
                    ->url(fn (Convocatoria $record): string => url('/admin/convocatorias/'.$record->id.'/edit')),
            ])
            ->paginated(false)
            ->emptyStateHeading('No hay convocatorias todavía')
            ->emptyStateDescription('Crea la primera convocatoria desde el menú lateral.')
            ->emptyStateIcon('heroicon-o-megaphone');
    }
}
