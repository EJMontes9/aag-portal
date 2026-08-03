<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers\ItemsRelationManager;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Menús';
    protected static ?string $modelLabel = 'Menú';
    protected static ?string $pluralModelLabel = 'Menús';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')->label('Slug (opcional)')->maxLength(255)->helperText('Se genera automáticamente si se deja vacío'),
            Forms\Components\Select::make('location')
                ->label('Ubicación')
                ->options([
                    'topbar' => 'Topbar (barra azul superior)',
                    'header' => 'Header (menú principal)',
                    'footer' => 'Footer',
                    'footer_secondary' => 'Footer secundario',
                    'sidebar' => 'Barra lateral',
                    'quick_links' => 'Enlaces rápidos',
                ])
                ->required(),
            Forms\Components\Textarea::make('description')->label('Descripción')->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->label('Activo')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
                Tables\Columns\TextColumn::make('location')->label('Ubicación')->badge(),
                Tables\Columns\TextColumn::make('all_items_count')->label('Items')->counts('allItems'),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
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

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
