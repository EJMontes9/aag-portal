<?php

namespace App\Filament\Resources\MenuResource\RelationManagers;

use App\Models\MenuItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'allItems';
    protected static ?string $title = 'Items del menu';
    protected static ?string $modelLabel = 'item';
    protected static ?string $pluralModelLabel = 'items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('label')
                ->label('Etiqueta')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('url')
                ->label('URL')
                ->helperText('Ruta relativa (ej: /noticias) o URL completa. Dejar vacio si es un dropdown (solo grupo de submenus).'),
            Forms\Components\Select::make('parent_id')
                ->label('Item padre (opcional)')
                ->options(fn () => MenuItem::where('menu_id', $this->ownerRecord->id)
                    ->whereNull('parent_id')
                    ->orderBy('sort_order')
                    ->pluck('label', 'id'))
                ->searchable()
                ->placeholder('Sin padre (item de primer nivel)')
                ->helperText('Si selecciona un padre, este item aparecera como submenu desplegable.'),
            Forms\Components\Select::make('target')
                ->label('Abrir en')
                ->options(['_self' => 'Misma ventana', '_blank' => 'Nueva ventana'])
                ->default('_self'),
            Forms\Components\TextInput::make('icon')
                ->label('Icono (clase o nombre)')
                ->placeholder('heroicon-o-home')
                ->helperText('Opcional. Para footer/sidebar mayormente.'),
            Forms\Components\TextInput::make('sort_order')
                ->label('Orden')
                ->numeric()
                ->default(0)
                ->helperText('Menor numero aparece primero.'),
            Forms\Components\Toggle::make('is_active')
                ->label('Activo')
                ->default(true),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Etiqueta')
                    ->formatStateUsing(fn ($state, $record) => $record->parent_id ? '— '.$state : $state)
                    ->weight(fn ($record) => $record->parent_id ? 'normal' : 'medium'),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->placeholder('(dropdown)')
                    ->color(fn ($state) => $state ? 'gray' : 'warning'),
                Tables\Columns\TextColumn::make('parent.label')
                    ->label('Padre')
                    ->placeholder('Raiz')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->size('sm')
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\Filter::make('only_roots')
                    ->label('Solo de primer nivel')
                    ->query(fn ($q) => $q->whereNull('parent_id')),
                Tables\Filters\Filter::make('only_children')
                    ->label('Solo submenus')
                    ->query(fn ($q) => $q->whereNotNull('parent_id')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nuevo item')
                    ->mutateFormDataUsing(fn (array $data) => array_merge($data, ['menu_id' => $this->ownerRecord->id])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
