<?php

namespace App\Filament\Resources\FormBuilderResource\RelationManagers;

use App\Models\FormField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class FieldsRelationManager extends RelationManager
{
    protected static string $relationship = 'fields';
    protected static ?string $title       = 'Campos del formulario';
    protected static ?string $icon        = 'heroicon-o-list-bullet';

    // ─── FORM ─────────────────────────────────────────────────────────────────

    public function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Definición del campo')
                ->schema([
                    Forms\Components\TextInput::make('label')
                        ->label('Etiqueta / pregunta')
                        ->placeholder('Ej: Nombre completo')
                        ->required()
                        ->maxLength(120)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation === 'create') {
                                $set('field_key', Str::snake(Str::ascii($state)));
                            }
                        })
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('field_key')
                        ->label('Clave interna')
                        ->placeholder('nombre_completo')
                        ->required()
                        ->maxLength(80)
                        ->rules(['regex:/^[a-z0-9_]+$/'])
                        ->helperText('Solo letras minúsculas, números y guión bajo. Auto-generado al escribir la etiqueta.')
                        ->columnSpan(2),

                    Forms\Components\Select::make('type')
                        ->label('Tipo de campo')
                        ->options(FormField::typeOptions())
                        ->required()
                        ->default('text')
                        ->live()
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('placeholder')
                        ->label('Texto de ejemplo (placeholder)')
                        ->maxLength(200)
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('help_text')
                        ->label('Texto de ayuda')
                        ->rows(2)
                        ->maxLength(300)
                        ->helperText('Aparece debajo del campo en el formulario.')
                        ->columnSpanFull(),
                ])->columns(4),

            Forms\Components\Section::make('Validación')
                ->schema([
                    Forms\Components\Toggle::make('required')
                        ->label('Campo obligatorio')
                        ->default(false),

                    Forms\Components\TextInput::make('min_length')
                        ->label('Longitud mínima')
                        ->numeric()
                        ->minValue(1)
                        ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'textarea'])),

                    Forms\Components\TextInput::make('max_length')
                        ->label('Longitud máxima')
                        ->numeric()
                        ->minValue(1)
                        ->visible(fn (Forms\Get $get) => in_array($get('type'), ['text', 'textarea'])),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Orden')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),
                ])->columns(4),

            Forms\Components\Section::make('Opciones')
                ->description('Define las opciones que verá el visitante en el desplegable o botones.')
                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['select', 'radio']))
                ->schema([
                    Forms\Components\Repeater::make('options')
                        ->label('Opciones disponibles')
                        ->schema([
                            Forms\Components\TextInput::make('label')
                                ->label('Texto visible')
                                ->placeholder('Ej: Consulta general')
                                ->required()
                                ->maxLength(100)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $set('value', Str::slug($state));
                                }),
                            Forms\Components\TextInput::make('value')
                                ->label('Valor interno')
                                ->placeholder('consulta_general')
                                ->required()
                                ->maxLength(80),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->addActionLabel('Añadir opción')
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Nueva opción')
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Toggle::make('is_active')
                ->label('Campo activo')
                ->default(true),
        ]);
    }

    // ─── TABLE ────────────────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->width(40),

                Tables\Columns\TextColumn::make('label')
                    ->label('Etiqueta')
                    ->searchable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('field_key')
                    ->label('Clave')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => FormField::typeOptions()[$state] ?? $state)
                    ->colors([
                        'info'    => fn ($state) => in_array($state, ['text', 'email', 'tel', 'number', 'date']),
                        'warning' => fn ($state) => in_array($state, ['select', 'radio']),
                        'success' => fn ($state) => $state === 'textarea',
                        'gray'    => fn ($state) => $state === 'checkbox',
                    ]),

                Tables\Columns\IconColumn::make('required')
                    ->label('Requerido')
                    ->boolean(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activo'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Añadir campo'),
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
}
