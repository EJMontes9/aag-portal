<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LotaipYearResource\Pages;
use App\Models\LotaipYear;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LotaipYearResource extends Resource
{
    protected static ?string $model = LotaipYear::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Transparencia';
    protected static ?string $navigationLabel = 'Años (LOTAIP / Rendición)';
    protected static ?string $modelLabel = 'año';
    protected static ?string $pluralModelLabel = 'años';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('section')
                ->label('Sección')
                ->options([
                    'lotaip' => 'LOTAIP / Transparencia',
                    'rendicion' => 'Rendición de cuentas',
                ])
                ->required()
                ->default('lotaip'),
            Forms\Components\TextInput::make('year')
                ->label('Año')
                ->required()
                ->numeric()
                ->minValue(2000)
                ->maxValue(2100),
            Forms\Components\CheckboxList::make('allowed_extensions')
                ->label('Extensiones permitidas (filtro global del año)')
                ->options([
                    'pdf' => 'PDF',
                    'csv' => 'CSV',
                    'xlsx' => 'Excel (xlsx)',
                    'doc' => 'Word (doc)',
                    'docx' => 'Word (docx)',
                ])
                ->columns(3)
                ->helperText('Si dejas vacío, se muestran todas las extensiones. Esto se puede sobrescribir por cada mes.'),
            Forms\Components\Toggle::make('is_active')->label('Activo')->default(true),
            Forms\Components\TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('year')->label('Año')->weight('medium')->size('lg'),
                Tables\Columns\TextColumn::make('section')
                    ->label('Sección')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'lotaip' ? 'LOTAIP' : 'Rendición'),
                Tables\Columns\TextColumn::make('allowed_extensions')
                    ->label('Filtro extensiones')
                    ->formatStateUsing(fn ($state) => empty($state) ? 'Todas' : strtoupper(implode(', ', is_array($state) ? $state : json_decode($state, true) ?? [])))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('months_count')->label('Meses')->counts('months'),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->defaultSort('year', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('section')->options(['lotaip' => 'LOTAIP', 'rendicion' => 'Rendición']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLotaipYears::route('/'),
            'create' => Pages\CreateLotaipYear::route('/create'),
            'edit' => Pages\EditLotaipYear::route('/{record}/edit'),
        ];
    }
}
