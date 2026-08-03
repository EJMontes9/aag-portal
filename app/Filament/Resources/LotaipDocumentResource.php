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
                                    $section = $m->year?->section === 'lotaip' ? 'LOTAIP' : 'Rendición';
                                    return [$m->id => "{$m->year?->year} · {$m->name} ({$section})"];
                                });
                        })
                        ->required()
                        ->searchable(),
                    Forms\Components\TextInput::make('title')
                        ->label('Título del documento')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej: Literal b2 - Distributivo del personal')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('literal')
                        ->label('Literal LOTAIP (opcional)')
                        ->maxLength(10)
                        ->placeholder('a, b1, b2, c, d...')
                        ->helperText('Útil para clasificar por artículo de la LOTAIP.'),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Orden')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Activo')
                        ->default(true),
                ])->columns(2),

            Forms\Components\Section::make('Archivo')
                ->description('El documento puede estar subido a este portal o vivir en el subdominio de documentos.')
                ->schema([
                    Forms\Components\Radio::make('source')
                        ->label('¿Dónde está el archivo?')
                        ->options([
                            LotaipDocument::SOURCE_EXTERNAL => 'En el subdominio de documentos (se sube por FTP)',
                            LotaipDocument::SOURCE_LOCAL    => 'Subirlo a este portal',
                        ])
                        ->descriptions([
                            LotaipDocument::SOURCE_EXTERNAL => 'Indica la ruta del archivo dentro del subdominio. No se sube nada desde aquí.',
                            LotaipDocument::SOURCE_LOCAL    => 'El archivo se guarda en el hosting de este portal.',
                        ])
                        // Por defecto, el subdominio: es donde la AAG viene
                        // publicando los documentos, y donde apuntan los
                        // enlaces ya difundidos.
                        ->default(LotaipDocument::SOURCE_EXTERNAL)
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    // ── Externo ──────────────────────────────────────────────
                    Forms\Components\TextInput::make('file_path')
                        ->label('Ruta o URL del archivo')
                        ->required()
                        ->maxLength(2000)
                        ->placeholder('2026/01/literal-b2-distributivo.pdf')
                        ->helperText(function () {
                            $base = LotaipDocument::baseUrlExterna();

                            if ($base === '') {
                                return '⚠ Todavía no has configurado la dirección del subdominio. Ve a Ajustes del sitio › Documentos. '
                                     . 'Mientras tanto puedes pegar la URL completa del archivo.';
                            }

                            return 'Ruta dentro de ' . $base . '/ — por ejemplo "2026/01/informe.pdf". '
                                 . 'También puedes pegar una URL completa si el archivo está en otro sitio.';
                        })
                        ->visible(fn (Forms\Get $get) => $get('source') === LotaipDocument::SOURCE_EXTERNAL)
                        ->rules([
                            // Si es URL absoluta, solo http/https: un
                            // "javascript:..." se ejecutaria al pulsar el enlace.
                            function () {
                                return function (string $attribute, $value, \Closure $fail) {
                                    $valor = trim((string) $value);
                                    if ($valor === '') {
                                        return;
                                    }
                                    // Sin exigir "//": "javascript:alert(1)" no
                                    // lo lleva y debe rechazarse igualmente.
                                    if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $valor)) {
                                        $esquema = strtolower((string) parse_url($valor, PHP_URL_SCHEME));
                                        if (! in_array($esquema, ['http', 'https'], true)) {
                                            $fail('La dirección debe empezar por http:// o https://, o ser una ruta como "2026/01/informe.pdf".');
                                        }
                                    }
                                };
                            },
                        ])
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('vista_previa_url')
                        ->label('Enlace resultante')
                        ->content(function (Forms\Get $get) {
                            $ruta = trim((string) $get('file_path'));
                            if ($ruta === '') {
                                return '—';
                            }
                            if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $ruta)) {
                                return $ruta;
                            }
                            $base = LotaipDocument::baseUrlExterna();

                            return $base === ''
                                ? 'Configura primero la dirección del subdominio en Ajustes del sitio.'
                                : $base . '/' . ltrim($ruta, '/');
                        })
                        ->visible(fn (Forms\Get $get) => $get('source') === LotaipDocument::SOURCE_EXTERNAL)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('file_size')
                        ->label('Tamaño en bytes (opcional)')
                        ->numeric()
                        ->helperText('Solo si quieres que se muestre junto al documento. En los archivos del subdominio no se puede calcular solo.')
                        ->visible(fn (Forms\Get $get) => $get('source') === LotaipDocument::SOURCE_EXTERNAL),

                    // ── Local ────────────────────────────────────────────────
                    Forms\Components\FileUpload::make('file_path')
                        ->label('Archivo')
                        ->required()
                        ->disk('public')
                        ->directory('lotaip')
                        // Sin preserveFilenames: conservar el nombre original
                        // permite subir un "informe.php" cuyo contenido pase por
                        // PDF. Filament renombra a un identificador propio.
                        ->acceptedFileTypes([
                            'application/pdf',
                            'text/csv',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ])
                        ->maxSize(51200) // 50 MB
                        ->helperText('Formatos: PDF, CSV, Excel, Word. Máximo 50 MB.')
                        ->visible(fn (Forms\Get $get) => $get('source') === LotaipDocument::SOURCE_LOCAL)
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
