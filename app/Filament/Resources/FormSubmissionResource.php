<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormSubmissionResource\Pages;
use App\Models\Form;
use App\Models\FormSubmission;
use Filament\Forms\Form as FilamentForm;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static ?string $navigationIcon   = 'heroicon-o-inbox';
    protected static ?string $navigationGroup  = 'Contenido';
    protected static ?string $navigationLabel  = 'Respuestas';
    protected static ?string $modelLabel       = 'Respuesta';
    protected static ?string $pluralModelLabel = 'Respuestas recibidas';
    protected static ?int    $navigationSort   = 51;

    // Badge con mensajes no leídos
    public static function getNavigationBadge(): ?string
    {
        $count = FormSubmission::unread()->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    // ─── FORM (solo se usa en vista, no creamos submissions desde Filament) ─

    public static function form(FilamentForm $form): FilamentForm
    {
        return $form->schema([]);
    }

    // ─── INFOLIST (vista detalle de una respuesta) ────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información del envío')
                    ->schema([
                        Infolists\Components\TextEntry::make('form.name')
                            ->label('Formulario'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Recibido')
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('ip_address')
                            ->label('IP'),
                        Infolists\Components\IconEntry::make('read_at')
                            ->label('Leído')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-clock'),
                    ])->columns(4),

                Infolists\Components\Section::make('Respuestas del visitante')
                    ->schema(function (FormSubmission $record): array {
                        $entries = [];
                        $form = $record->form()->with('fields')->first();
                        $fields = $form?->fields ?? collect();

                        foreach ($record->data as $key => $value) {
                            // Busca la etiqueta del campo por field_key
                            $field  = $fields->firstWhere('field_key', $key);
                            $label  = $field?->label ?? $key;
                            $type   = $field?->type ?? 'text';

                            $entry = Infolists\Components\TextEntry::make("data.{$key}")
                                ->label($label)
                                ->default('—');

                            if ($type === 'textarea' || strlen((string) $value) > 80) {
                                $entry = $entry->columnSpanFull();
                            }

                            if ($type === 'checkbox') {
                                $entry = $entry->formatStateUsing(fn ($state) => $state ? 'Sí' : 'No');
                            }

                            $entries[] = $entry;
                        }

                        return $entries;
                    })->columns(2),
            ]);
    }

    // ─── TABLE ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('read_at')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('warning')
                    ->tooltip(fn ($record) => $record->isRead() ? 'Leído' : 'Sin leer')
                    ->width(40),

                Tables\Columns\TextColumn::make('form.name')
                    ->label('Formulario')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('preview')
                    ->label('Vista previa')
                    ->getStateUsing(function (FormSubmission $record): string {
                        $values = array_values(array_filter($record->data));
                        return implode(' · ', array_slice(
                            array_map(fn ($v) => is_string($v) ? Str($v)->limit(40) : $v, $values),
                            0, 3
                        ));
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recibido')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('form')
                    ->label('Formulario')
                    ->relationship('form', 'name'),

                Tables\Filters\Filter::make('unread')
                    ->label('Solo sin leer')
                    ->query(fn ($query) => $query->whereNull('read_at'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->after(function (FormSubmission $record) {
                        $record->markAsRead();
                    }),

                Tables\Actions\Action::make('toggle_read')
                    ->label(fn (FormSubmission $r) => $r->isRead() ? 'Marcar no leído' : 'Marcar leído')
                    ->icon(fn (FormSubmission $r) => $r->isRead() ? 'heroicon-o-envelope' : 'heroicon-o-envelope-open')
                    ->color('gray')
                    ->action(fn (FormSubmission $r) => $r->isRead() ? $r->markAsUnread() : $r->markAsRead()),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_read')
                        ->label('Marcar como leídos')
                        ->icon('heroicon-o-envelope-open')
                        ->action(fn ($records) => $records->each->markAsRead()),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormSubmissions::route('/'),
            'view'  => Pages\ViewFormSubmission::route('/{record}'),
        ];
    }
}
