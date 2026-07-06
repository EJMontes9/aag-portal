<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormBuilderResource\Pages;
use App\Filament\Resources\FormBuilderResource\RelationManagers;
use App\Models\Form;
use Filament\Forms;
use Filament\Forms\Form as FilamentForm;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FormBuilderResource extends Resource
{
    protected static ?string $model = Form::class;

    protected static ?string $navigationIcon       = 'heroicon-o-document-text';
    protected static ?string $navigationGroup      = 'Contenido';
    protected static ?string $navigationLabel      = 'Formularios';
    protected static ?string $modelLabel           = 'Formulario';
    protected static ?string $pluralModelLabel     = 'Formularios';
    protected static ?int    $navigationSort       = 50;

    // Badge con total de formularios activos
    public static function getNavigationBadge(): ?string
    {
        return (string) Form::active()->count() ?: null;
    }

    // ─── FORM ─────────────────────────────────────────────────────────────────

    public static function form(FilamentForm $form): FilamentForm
    {
        return $form->schema([

            Forms\Components\Section::make('Información del formulario')
                ->description('Define el nombre, slug y textos que verá el visitante.')
                ->icon('heroicon-o-information-circle')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del formulario')
                        ->placeholder('Ej: Formulario de contacto')
                        ->required()
                        ->maxLength(120)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation === 'create') {
                                $set('slug', Str::slug($state));
                            }
                        })
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug (identificador único)')
                        ->placeholder('contacto')
                        ->required()
                        ->maxLength(100)
                        ->unique(Form::class, 'slug', ignoreRecord: true)
                        ->helperText('Solo letras minúsculas, números y guiones. No puede repetirse.')
                        ->rules(['regex:/^[a-z0-9\-]+$/'])
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('description')
                        ->label('Descripción / instrucciones')
                        ->placeholder('Escríbenos y te responderemos en menos de 24 horas.')
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])->columns(4),

            Forms\Components\Section::make('Configuración del envío')
                ->description('Qué sucede cuando el visitante envía el formulario.')
                ->icon('heroicon-o-paper-airplane')
                ->schema([
                    Forms\Components\TextInput::make('submit_label')
                        ->label('Texto del botón enviar')
                        ->default('Enviar mensaje')
                        ->required()
                        ->maxLength(60),

                    Forms\Components\Textarea::make('success_message')
                        ->label('Mensaje de éxito')
                        ->default('¡Gracias! Tu mensaje ha sido enviado correctamente. Nos pondremos en contacto pronto.')
                        ->required()
                        ->rows(2)
                        ->maxLength(300)
                        ->helperText('Mensaje que ve el visitante tras enviar correctamente.'),

                    Forms\Components\TagsInput::make('notify_emails')
                        ->label('Correos de notificación')
                        ->placeholder('Añadir email y presionar Enter')
                        ->helperText('Cada vez que alguien envíe el formulario, se notificará a estas direcciones.')
                        ->nestedRecursiveRules(['email'])
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('store_submissions')
                        ->label('Guardar respuestas en la base de datos')
                        ->default(true)
                        ->helperText('Si está activo, las respuestas quedan disponibles en el panel bajo "Respuestas".'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Formulario activo')
                        ->default(true),
                ])->columns(2),

        ]);
    }

    // ─── TABLE ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('fields_count')
                    ->label('Campos')
                    ->counts('fields')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('submissions_count')
                    ->label('Respuestas')
                    ->counts('submissions')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('unread_submissions_count')
                    ->label('Sin leer')
                    ->counts('unreadSubmissions')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Estado'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ─── RELATION MANAGERS ───────────────────────────────────────────────────

    public static function getRelations(): array
    {
        return [
            RelationManagers\FieldsRelationManager::class,
        ];
    }

    // ─── PAGES ───────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListForms::route('/'),
            'create' => Pages\CreateForm::route('/create'),
            'edit'   => Pages\EditForm::route('/{record}/edit'),
        ];
    }
}
