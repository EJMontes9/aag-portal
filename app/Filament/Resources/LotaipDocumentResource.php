<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LotaipDocumentResource\Pages;
use App\Models\LotaipDocument;
use App\Models\LotaipMonth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LotaipDocumentResource extends Resource
{
    protected static ?string $model = LotaipDocument::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-down';
    protected static ?string $navigationGroup = 'Transparencia';
    protected static ?string $navigationLabel = 'Documentos';
    protected static ?string $modelLabel = 'documento';
    protected static ?string $pluralModelLabel = 'documentos';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del documento')
                ->schema([
                    Forms\Components\Select::make('month_id')
                        ->label('Año y mes')
                        ->options(function () {
                            return LotaipMonth::with('year')
                                ->get()
                                ->mapWithKeys(function ($m) {
                                    $section = $m->year?->section === 'lotaip' ? 'LOTAIP' : 'Rendicion';
                                    return [$m->id => "{$m->year?->year} · {$m->name} ({$section})"];
                                });
                        })
                        ->required()
                        ->searchable(),
                    Forms\Components\TextInput::make('title')
                        ->label('Titulo del documento')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Literal b2 - Distributivo del personal')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('literal')
                        ->label('Literal LOTAIP (opcional)')
                        ->maxLength(10)
                        ->placeholder('a, b1, b2, c, d...')
                        ->helperText('Util para clasificar por articulo de la LOTAIP.'),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Orden')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Activo')
                        ->default(true),
                ])->columns(2),

            Forms\Components\Section::make('Archivo')
                ->schema([
                    Forms\Components\FileUpload::make('file_path')
                        ->label('Archivo')
                        ->required()
                        ->disk('public')
                        ->directory('lotaip')
                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'application/pdf',
                            'text/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->maxSize(51200) // 50 MB
                        ->helperText('Formatos: PDF, CSV, Excel, Word. Maximo 50 MB.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Documento')
                    ->searchable()
                    ->wrap()
                    ->limit(70)
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('literal')
                    ->label('Literal')
                    ->badge()
                    ->placeholder('—')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('month.year.year')
                    ->label('Año')
                    ->sortable(),
                Tables\Columns\TextColumn::make('month.month')
                    ->label('Mes')
                    ->formatStateUsing(fn ($state) => LotaipMonth::MONTH_NAMES[$state] ?? "Mes $state"),
                Tables\Columns\TextColumn::make('extension')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pdf' => 'danger',
                        'csv' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Tamaño')
                    ->formatStateUsing(fn ($r) => $r ? (new LotaipDocument(['file_size' => $r]))->size_human : '—'),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('extension')
                    ->options([
                        'pdf' => 'PDF',
                        'csv' => 'CSV',
                        'xlsx' => 'Excel',
                    ]),
                Tables\Filters\SelectFilter::make('month_id')
                    ->label('Mes')
                    ->relationship('month', 'month'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Descargar')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn (LotaipDocument $r) => $r->url)
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLotaipDocuments::route('/'),
            'create' => Pages\CreateLotaipDocument::route('/create'),
            'edit' => Pages\EditLotaipDocument::route('/{record}/edit'),
        ];
    }
}
