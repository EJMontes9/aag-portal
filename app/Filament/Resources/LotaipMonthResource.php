<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LotaipMonthResource\Pages;
use App\Models\LotaipMonth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LotaipMonthResource extends Resource
{
    protected static ?string $model = LotaipMonth::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Transparencia';
    protected static ?string $navigationLabel = 'Meses (LOTAIP / Rendición)';
    protected static ?string $modelLabel = 'mes';
    protected static ?string $pluralModelLabel = 'meses';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Período')
                ->schema([
                    Forms\Components\Select::make('year_id')
                        ->label('Año')
                        ->relationship('year', 'year', fn ($q) => $q->orderBy('year', 'desc'))
                        ->getOptionLabelFromRecordUsing(fn ($r) => "{$r->year} (" . ($r->section === 'lotaip' ? 'LOTAIP' : 'Rendición') . ")")
                        ->required()
                        ->searchable()
                        ->preload(),
                    Forms\Components\Select::make('month')
                        ->label('Mes')
                        ->options(LotaipMonth::MONTH_NAMES)
                        ->required(),
                    Forms\Components\Toggle::make('is_active')->label('Activo')->default(true),
                ])->columns(3),

            Forms\Components\Section::make('Modo de presentación')
                ->schema([
                    Forms\Components\Select::make('mode')
                        ->label('¿Qué mostrar al visitante?')
                        ->options([
                            'files' => 'Archivos subidos al sistema',
                            'redirect' => 'Redirigir a URL externa (ej. transparencia activa)',
                        ])
                        ->default('files')
                        ->required()
                        ->live(),
                    Forms\Components\TextInput::make('redirect_url')
                        ->label('URL externa')
                        ->url()
                        ->placeholder('https://www.transparencia.gob.ec/...')
                        ->visible(fn ($get) => $get('mode') === 'redirect')
                        ->required(fn ($get) => $get('mode') === 'redirect'),
                    Forms\Components\TextInput::make('redirect_label')
                        ->label('Texto del enlace (opcional)')
                        ->placeholder('Transparencia activa')
                        ->visible(fn ($get) => $get('mode') === 'redirect'),
                    Forms\Components\CheckboxList::make('allowed_extensions')
                        ->label('Override de extensiones permitidas (solo este mes)')
                        ->options([
                            'pdf' => 'PDF',
                            'csv' => 'CSV',
                            'xlsx' => 'Excel (xlsx)',
                            'doc' => 'Word (doc)',
                            'docx' => 'Word (docx)',
                        ])
                        ->columns(3)
                        ->helperText('Si dejas vacío, hereda del año. Útil para meses con regla diferente.')
                        ->visible(fn ($get) => $get('mode') === 'files'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('year.year')->label('Año')->sortable()->weight('medium'),
                Tables\Columns\TextColumn::make('month')
                    ->label('Mes')
                    ->formatStateUsing(fn ($state) => LotaipMonth::MONTH_NAMES[$state] ?? "Mes $state")
                    ->sortable(),
                Tables\Columns\TextColumn::make('year.section')
                    ->label('Sección')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'lotaip' ? 'LOTAIP' : 'Rendición'),
                Tables\Columns\TextColumn::make('mode')
                    ->label('Modo')
                    ->badge()
                    ->color(fn ($state) => $state === 'redirect' ? 'warning' : 'success')
                    ->formatStateUsing(fn ($state) => $state === 'redirect' ? 'Redirige' : 'Archivos'),
                Tables\Columns\TextColumn::make('documents_count')->label('Documentos')->counts('documents'),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->defaultSort('year.year', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('year_id')
                    ->label('Año')
                    ->relationship('year', 'year'),
                Tables\Filters\SelectFilter::make('mode')
                    ->options(['files' => 'Archivos', 'redirect' => 'Redirige']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLotaipMonths::route('/'),
            'create' => Pages\CreateLotaipMonth::route('/create'),
            'edit' => Pages\EditLotaipMonth::route('/{record}/edit'),
        ];
    }
}
