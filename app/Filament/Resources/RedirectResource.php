<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Redirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Gestion de redirecciones de direcciones antiguas.
 *
 * Existe para que el personal de la AAG pueda arreglar un enlace roto sin
 * pedir un despliegue: aparece un 404 en el informe de rastreo, se anade la
 * redireccion desde aqui y queda resuelto al momento.
 */
class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int    $navigationSort  = 46;

    protected static ?string $modelLabel       = 'redirección';
    protected static ?string $pluralModelLabel = 'redirecciones';
    protected static ?string $navigationLabel  = 'Redirecciones';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Redirección')
                ->description('Envía una dirección que ya no existe a su equivalente en el portal nuevo. Sirve para que los enlaces antiguos, los de Google y los que la gente tiene guardados sigan funcionando.')
                ->schema([
                    Forms\Components\TextInput::make('from_path')
                        ->label('Dirección antigua')
                        ->required()
                        ->maxLength(500)
                        ->unique(ignoreRecord: true)
                        ->placeholder('/quienes-somos')
                        ->helperText('Solo la parte que va después del dominio, empezando por barra. Sin https:// ni el nombre del sitio.')
                        // Se normaliza al guardar para que "/Quienes-Somos/" y
                        // "quienes-somos" no acaben como dos filas distintas que
                        // nunca llegan a coincidir con lo que pide el navegador.
                        ->dehydrateStateUsing(fn (?string $state) => $state ? Redirect::normalizar($state) : $state),

                    Forms\Components\TextInput::make('to_path')
                        ->label('Dirección nueva')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('/nosotros')
                        ->helperText('Una ruta de este portal (/nosotros) o una dirección completa que empiece por https://')
                        ->rules([
                            // Misma comprobacion que hace el middleware. Se repite
                            // aqui para avisar al escribirlo, en vez de guardar
                            // una redireccion que luego se ignora en silencio.
                            fn () => function (string $attribute, $value, \Closure $fail) {
                                $v = (string) $value;
                                if (str_starts_with($v, '//')) {
                                    $fail('No se admite una dirección que empiece por //, porque apunta a otro sitio web.');
                                    return;
                                }
                                if (! str_starts_with($v, '/') && ! str_starts_with(mb_strtolower($v), 'https://')) {
                                    $fail('Debe ser una ruta interna (empezando por /) o una dirección https://');
                                }
                            },
                        ]),

                    Forms\Components\Select::make('status_code')
                        ->label('Tipo')
                        ->options([
                            301 => '301 — Permanente (recomendado)',
                            302 => '302 — Temporal',
                        ])
                        ->default(301)
                        ->required()
                        ->helperText('Permanente traslada a Google el posicionamiento de la dirección antigua. Usa temporal solo si el cambio va a revertirse.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Activa')
                        ->default(true),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->maxLength(500)
                        ->rows(2)
                        ->helperText('Opcional. Por ejemplo, de dónde salió este enlace.')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_path')
                    ->label('Dirección antigua')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('to_path')
                    ->label('Va a')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $state === 301 ? 'success' : 'warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),

                Tables\Columns\TextColumn::make('hits')
                    ->label('Visitas')
                    ->sortable()
                    ->description('veces que se usó'),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Último uso')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('nunca')
                    ->sortable(),
            ])
            ->defaultSort('hits', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Activa'),
                Tables\Filters\Filter::make('sin_uso')
                    ->label('Nunca usadas')
                    ->query(fn ($query) => $query->whereNull('last_used_at')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Sin redirecciones')
            ->emptyStateDescription('Cuando el portal sustituya al sitio anterior, aquí se añaden las direcciones antiguas para que sigan funcionando.');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit'   => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}
