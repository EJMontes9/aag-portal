<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqCategoryResource\Pages;
use App\Models\FaqCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqCategoryResource extends Resource
{
    protected static ?string $model = FaqCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Categorias FAQ';
    protected static ?string $modelLabel = 'categoria FAQ';
    protected static ?string $pluralModelLabel = 'categorias FAQ';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(80),
            Forms\Components\TextInput::make('slug')->label('Slug (URL)')->maxLength(80)->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('icon')->label('Heroicon (opcional)')->placeholder('heroicon-o-plane'),
            Forms\Components\TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->color('gray')->fontFamily('mono')->size('sm'),
                Tables\Columns\TextColumn::make('icon')->label('Icono')->placeholder('—')->color('gray')->size('sm'),
                Tables\Columns\TextColumn::make('faqs_count')->label('Preguntas')->counts('faqs'),
                Tables\Columns\TextColumn::make('sort_order')->label('Orden')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqCategories::route('/'),
            'create' => Pages\CreateFaqCategory::route('/create'),
            'edit' => Pages\EditFaqCategory::route('/{record}/edit'),
        ];
    }
}
