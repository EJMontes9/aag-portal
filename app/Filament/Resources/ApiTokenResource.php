<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiTokenResource\Pages;
use App\Models\ApiToken;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Tokens de acceso a la API publica (ver docs/API.md).
 *
 * No hay pantallas de crear ni de editar: un token no se edita, y su creacion
 * necesita ensenar el valor en claro una sola vez, cosa que un formulario
 * normal de Filament no hace. Ambas cosas se resuelven con acciones en el
 * listado (ver Pages\ListApiTokens).
 */
class ApiTokenResource extends Resource
{
    protected static ?string $model = ApiToken::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Configuracion';
    protected static ?string $navigationLabel = 'Tokens de API';
    protected static ?string $modelLabel = 'token de API';
    protected static ?string $pluralModelLabel = 'tokens de API';
    protected static ?int $navigationSort = 20;
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Doble candado a proposito.
     *
     * El permiso de Shield ya restringe el acceso, pero un token de API es una
     * credencial: basta con que alguien asigne el permiso al rol equivocado
     * para que un editor pueda emitir accesos. La comprobacion de rol es la
     * barrera que no depende de como esten repartidos los permisos hoy.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user
            && $user->hasAnyRole(['super_admin', 'admin'])
            && parent::canAccess();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn (ApiToken $r) => 'Emitido por ' . ($r->tokenable?->name ?? '—')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Último uso')
                    ->dateTime('d/m/Y H:i')
                    // Un token que nunca se ha usado suele ser un token que
                    // sobra: conviene que salte a la vista.
                    ->placeholder('Nunca usado')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No hay tokens emitidos')
            ->emptyStateDescription('La API solo responde a peticiones con un token válido. Crea uno cuando una integración lo necesite.')
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Revocar')
                    ->icon('heroicon-m-no-symbol')
                    ->modalHeading('Revocar token')
                    ->modalDescription('El token dejará de funcionar de inmediato y no se puede recuperar. Cualquier integración que lo esté usando empezará a recibir errores 401.')
                    ->modalSubmitActionLabel('Revocar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Revocar seleccionados'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiTokens::route('/'),
        ];
    }
}
