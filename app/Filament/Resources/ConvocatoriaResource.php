<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConvocatoriaResource\Pages;
use App\Models\Convocatoria;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConvocatoriaResource extends Resource
{
    protected static ?string $model = Convocatoria::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Convocatorias';
    protected static ?string $modelLabel = 'convocatoria';
    protected static ?string $pluralModelLabel = 'convocatorias';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make()
                ->tabs([
                    // ── TAB 1: TIPO Y CONTENIDO PRINCIPAL ──────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Tipo y Contenido')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Section::make('Configuración general')
                                ->schema([
                                    Forms\Components\Select::make('tipo')
                                        ->label('Tipo de publicación')
                                        ->options([
                                            'proceso' => '📋 Proceso de contratación — con cronograma y documentos descargables',
                                            'aviso'   => '📢 Aviso simple — anuncio, comunicado o invitación',
                                        ])
                                        ->default('proceso')
                                        ->required()
                                        ->live()
                                        ->helperText('El tipo determina cómo se muestra en el portal y qué campos están disponibles.'),

                                    Forms\Components\TextInput::make('title')
                                        ->label('Título')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('slug')
                                        ->label('Slug (URL amigable)')
                                        ->helperText('Se genera automáticamente si se deja vacío')
                                        ->maxLength(255),

                                    Forms\Components\Textarea::make('short_description')
                                        ->label('Descripción')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])->columns(2),

                            // ── Campos de AVISO ──────────────────────────────────────────────
                            Forms\Components\Section::make('🎨 Diseño del aviso')
                                ->description('Personaliza la apariencia del anuncio en el portal.')
                                ->visible(fn (Forms\Get $get) => $get('tipo') === 'aviso')
                                ->schema([
                                    Forms\Components\Select::make('layout_type')
                                        ->label('Layout visual')
                                        ->options([
                                            'poster'  => '🖼️  Póster — fondo navy, centrado (como un afiche)',
                                            'banner'  => '↔️  Banner horizontal — imagen izquierda + texto derecho',
                                            'minimal' => '✏️  Minimal — limpio, solo texto y logo',
                                        ])
                                        ->default('poster')
                                        ->required(),

                                    Forms\Components\Toggle::make('show_logo')
                                        ->label('Mostrar logo institucional AAG')
                                        ->default(true),

                                    Forms\Components\FileUpload::make('imagen')
                                        ->label('Imagen del aviso')
                                        ->helperText('Opcional. Para póster: icono o imagen decorativa. Para banner: imagen de fondo izquierda.')
                                        ->image()
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                        ->maxSize(4096)
                                        ->imageEditor()
                                        ->directory('convocatorias/imagenes')
                                        ->disk('public')
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('video_url')
                                        ->label('URL de video (YouTube o Vimeo)')
                                        ->helperText('Pega la URL del video. Ejemplo: https://www.youtube.com/watch?v=...')
                                        ->url()
                                        ->prefix('🎬')
                                        ->columnSpanFull(),
                                ])->columns(2),

                            // ── Campos de PROCESO ────────────────────────────────────────────
                            Forms\Components\Section::make('🏛️ Datos del proceso de contratación')
                                ->visible(fn (Forms\Get $get) => $get('tipo') === 'proceso')
                                ->schema([
                                    Forms\Components\Select::make('layout_type')
                                        ->label('Diseño visual del bloque')
                                        ->options([
                                            'split'   => '◫  Split — Info izquierda + Countdown derecha (recomendado)',
                                            'card'    => '▭  Tarjeta — Cabecera navy + cuerpo blanco',
                                            'minimal' => '▬  Minimal — Línea de acento lateral, limpio',
                                        ])
                                        ->default('split')
                                        ->selectablePlaceholder(false)
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('area')
                                        ->label('Área / Departamento')
                                        ->maxLength(100)
                                        ->placeholder('Ej: Dirección de Infraestructura'),
                                    Forms\Components\TextInput::make('modality')
                                        ->label('Modalidad')
                                        ->maxLength(100)
                                        ->placeholder('Ej: Consultoría Individual · Guayaquil'),
                                    // enlace_referencia eliminado — la página de detalle interna reemplaza esto
                                ])->columns(2),
                        ]),

                    // ── TAB 2: CRONOGRAMA ───────────────────────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Cronograma')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Forms\Components\Section::make()
                                ->description('Agrega las etapas del proceso. Para avisos: fecha y hora del evento. Para procesos: todas las fases de la contratación.')
                                ->schema([
                                    Forms\Components\Repeater::make('cronograma')
                                        ->label('Etapas del cronograma')
                                        ->schema([
                                            Forms\Components\TextInput::make('etapa')
                                                ->label('Etapa / Actividad')
                                                ->required()
                                                ->placeholder('Ej: Recepción de postulaciones')
                                                ->columnSpan(3),
                                            Forms\Components\DatePicker::make('fecha')
                                                ->label('Fecha')
                                                ->displayFormat('d/m/Y')
                                                ->native(false)
                                                ->columnSpan(2),
                                            Forms\Components\TextInput::make('hora')
                                                ->label('Hora (opcional)')
                                                ->placeholder('17:00')
                                                ->columnSpan(1),
                                        ])
                                        ->columns(6)
                                        ->defaultItems(0)
                                        ->reorderable()
                                        ->addActionLabel('+ Agregar etapa')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ── TAB 3: DOCUMENTOS ───────────────────────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Documentos')
                        ->icon('heroicon-o-paper-clip')
                        ->badge(fn ($record) => $record ? count((array)($record->documentos ?? [])) : null)
                        ->schema([
                            Forms\Components\Section::make()
                                ->description('Sube los documentos que los participantes podrán descargar (PDF, Word, Excel, ZIP, etc.).')
                                ->schema([
                                    Forms\Components\Repeater::make('documentos')
                                        ->label('Documentos descargables')
                                        ->schema([
                                            Forms\Components\TextInput::make('nombre')
                                                ->label('Nombre para mostrar')
                                                ->required()
                                                ->placeholder('Ej: Términos de Referencia')
                                                ->columnSpan(2),
                                            Forms\Components\FileUpload::make('archivo')
                                                ->label('Archivo')
                                                ->required()
                                                ->disk('public')
                                                ->directory('convocatorias/docs')
                                                ->acceptedFileTypes([
                                                    'application/pdf',
                                                    'application/msword',
                                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                    'application/vnd.ms-excel',
                                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                                    'application/vnd.ms-powerpoint',
                                                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                                    'application/zip',
                                                    'application/x-rar-compressed',
                                                    'application/x-7z-compressed',
                                                    'image/jpeg', 'image/png', 'image/webp',
                                                ])
                                                ->maxSize(51200)  // 50 MB
                                                ->downloadable()
                                                ->columnSpan(2),
                                        ])
                                        ->columns(4)
                                        ->defaultItems(0)
                                        ->reorderable()
                                        ->addActionLabel('+ Agregar documento')
                                        ->columnSpanFull(),

                                    // bases_pdf legacy — solo para procesos ya creados
                                    Forms\Components\FileUpload::make('bases_pdf')
                                        ->label('Bases en PDF (campo legacy — usa "Documentos" arriba para nuevos archivos)')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->maxSize(10240)
                                        ->directory('convocatorias')
                                        ->disk('public')
                                        ->helperText('Este campo se mantiene por compatibilidad con registros anteriores.')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ── TAB 4: REQUISITOS (solo procesos) ───────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Requisitos')
                        ->icon('heroicon-o-list-bullet')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Repeater::make('requirements')
                                        ->label('Requisitos mínimos')
                                        ->simple(Forms\Components\TextInput::make('requirement')->required())
                                        ->defaultItems(0)
                                        ->reorderable()
                                        ->addActionLabel('+ Agregar requisito')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // ── TAB 5: FECHAS, ESTADO Y ALERTAS ────────────────────────────────────
                    Forms\Components\Tabs\Tab::make('Estado y Alertas')
                        ->icon('heroicon-o-bell')
                        ->schema([
                            Forms\Components\Section::make('Fechas y estado')
                                ->schema([
                                    Forms\Components\DateTimePicker::make('opens_at')
                                        ->label('Fecha de apertura (opcional)'),
                                    Forms\Components\DateTimePicker::make('closes_at')
                                        ->label('Fecha de cierre')
                                        ->helperText('Para procesos: deadline de postulación. Para avisos: fecha en que deja de mostrarse (opcional).'),
                                    Forms\Components\Select::make('status')
                                        ->label('Estado')
                                        ->options([
                                            'borrador' => 'Borrador (no visible en el portal)',
                                            'vigente'  => 'Vigente (visible)',
                                            'cerrada'  => 'Cerrada',
                                        ])
                                        ->default('borrador')
                                        ->required(),
                                    Forms\Components\Toggle::make('featured_on_home')
                                        ->label('Destacar en el home (selección automática del bloque)'),
                                ])->columns(2),

                            Forms\Components\Section::make('Alerta al visitante')
                                ->description('Muestra una notificación flotante cuando el visitante entra al portal.')
                                ->schema([
                                    Forms\Components\Select::make('alert_mode')
                                        ->label('Modo de alerta')
                                        ->options([
                                            'none'   => 'Sin alerta (solo en la página)',
                                            'modal'  => 'Modal (ventana emergente)',
                                            'toast'  => 'Toast (notificación esquina)',
                                            'banner' => 'Banner (franja superior)',
                                        ])
                                        ->default('none')
                                        ->required()
                                        ->live(),
                                    Forms\Components\Select::make('alert_frequency')
                                        ->label('Frecuencia de aparición')
                                        ->options([
                                            'always'  => 'Siempre que visite la página',
                                            'session' => 'Una vez por sesión',
                                            'once'    => 'Solo la primera vez',
                                        ])
                                        ->default('session')
                                        ->visible(fn (Forms\Get $get) => $get('alert_mode') !== 'none'),
                                ])->columns(2),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('tipo')
                    ->label('Tipo')
                    ->colors([
                        'info'    => 'proceso',
                        'success' => 'aviso',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'proceso' => 'Proceso',
                        'aviso'   => 'Aviso',
                        default   => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(45)
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('area')
                    ->label('Área')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('closes_at')
                    ->label('Cierre')
                    ->dateTime('d M Y · H:i')
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'vigente'  => 'success',
                        'cerrada'  => 'gray',
                        'borrador' => 'warning',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'vigente'  => 'Vigente',
                        'cerrada'  => 'Cerrada',
                        'borrador' => 'Borrador',
                        default    => ucfirst($state),
                    }),
                Tables\Columns\IconColumn::make('featured_on_home')
                    ->label('Home')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('documentos')
                    ->label('Docs')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' doc(s)' : '—')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options(['proceso' => 'Proceso', 'aviso' => 'Aviso']),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['borrador' => 'Borrador', 'vigente' => 'Vigente', 'cerrada' => 'Cerrada']),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListConvocatorias::route('/'),
            'create' => Pages\CreateConvocatoria::route('/create'),
            'edit'   => Pages\EditConvocatoria::route('/{record}/edit'),
        ];
    }
}
