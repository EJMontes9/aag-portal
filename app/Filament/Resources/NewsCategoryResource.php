<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsCategoryResource\Pages;
use App\Models\NewsCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsCategoryResource extends Resource
{
    protected static ?string $model = NewsCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Categorías de noticias';
    protected static ?string $modelLabel = 'categoría';
    protected static ?string $pluralModelLabel = 'categorías de noticias';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(80),
            Forms\Components\TextInput::make('slug')
                ->label('Slug (URL)')
                ->maxLength(80)
                ->unique(ignoreRecord: true)
                ->helperText('Se autogenera del nombre si se deja vacío.'),
            Forms\Components\ColorPicker::make('color')
                ->label('Color del badge')
                ->helperText('Color hex para el chip de la categoría.'),
            Forms\Components\TextInput::make('sort_order')
                ->label('Orden')
                ->numeric()
                ->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->color('gray')
                    ->fontFamily('mono')
                    ->size('sm'),
                Tables\Columns\ColorColumn::make('color')->label('Color'),
                Tables\Columns\TextColumn::make('news_count')
                    ->label('Noticias')
                    ->counts('news'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsCategories::route('/'),
            'create' => Pages\CreateNewsCategory::route('/create'),
            'edit' => Pages\EditNewsCategory::route('/{record}/edit'),
        ];
    }
}
