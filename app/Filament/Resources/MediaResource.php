<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use App\Services\MediaService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon  = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Galería de medios';
    protected static ?string $modelLabel      = 'Archivo';
    protected static ?string $pluralModelLabel = 'Galería de medios';
    protected static ?int    $navigationSort  = 45;

    // Badge con total de archivos
    public static function getNavigationBadge(): ?string
    {
        return (string) Media::count() ?: null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // FORM  (para editar metadatos: nombre, alt_text, carpeta)
    // ──────────────────────────────────────────────────────────────────────────

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del archivo')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('alt_text')
                    ->label('Texto alternativo (SEO / accesibilidad)')
                    ->placeholder('Describe la imagen brevemente…')
                    ->maxLength(500)
                    ->columnSpanFull(),

                Forms\Components\Select::make('folder')
                    ->label('Carpeta')
                    ->options([
                        'general'     => 'General',
                        'noticias'    => 'Noticias',
                        'paginas'     => 'Páginas',
                        'documentos'  => 'Documentos',
                        'banners'     => 'Banners',
                        'logos'       => 'Logos',
                    ])
                    ->placeholder('Sin carpeta')
                    ->searchable(),
            ])->columns(2),

            Forms\Components\Section::make('Detalles del archivo')->schema([
                Forms\Components\Placeholder::make('type_info')
                    ->label('Tipo')
                    ->content(fn ($record) => $record?->type ?? '—'),

                Forms\Components\Placeholder::make('size_info')
                    ->label('Tamaño')
                    ->content(fn ($record) => $record?->size_formatted ?? '—'),

                Forms\Components\Placeholder::make('dimensions_info')
                    ->label('Dimensiones')
                    ->content(fn ($record) => $record?->dimensions ?? '—'),

                Forms\Components\Placeholder::make('path_info')
                    ->label('Ruta')
                    ->content(fn ($record) => $record?->path ?? '—'),
            ])->columns(2)->visibleOn('edit'),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // TABLE
    // ──────────────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('url')
                    ->label('')
                    ->width(72)
                    ->height(56)
                    ->defaultImageUrl(asset('images/file-placeholder.png'))
                    ->visibility(fn ($record) => $record->type === 'image' ? 'public' : null)
                    ->extraImgAttributes(['class' => 'object-cover rounded']),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->path)
                    ->limit(45),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'image',
                        'info'    => 'video',
                        'warning' => 'document',
                        'gray'    => 'other',
                    ]),

                Tables\Columns\TextColumn::make('size_formatted')
                    ->label('Tamaño')
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('size', $direction)),

                Tables\Columns\TextColumn::make('dimensions')
                    ->label('Dimensiones')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Subido')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'image'    => 'Imágenes',
                        'video'    => 'Videos',
                        'document' => 'Documentos',
                        'other'    => 'Otros',
                    ]),

                Tables\Filters\SelectFilter::make('folder')
                    ->label('Carpeta')
                    ->options([
                        'general'    => 'General',
                        'noticias'   => 'Noticias',
                        'paginas'    => 'Páginas',
                        'documentos' => 'Documentos',
                        'banners'    => 'Banners',
                        'logos'      => 'Logos',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('copy_url')
                    ->label('Copiar URL')
                    ->icon('heroicon-o-clipboard')
                    ->action(fn () => null) // handled by JS in view
                    ->extraAttributes(fn ($record) => [
                        'x-data'    => '',
                        'x-on:click' => "navigator.clipboard.writeText('{$record->url}'); \$el.innerText = '✓ Copiado'",
                    ]),

                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->after(fn ($record) => Storage::disk($record->disk)->delete($record->path)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function ($records) {
                            foreach ($records as $record) {
                                Storage::disk($record->disk)->delete($record->path);
                            }
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('upload')
                    ->label('Subir archivos')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->form([
                        Forms\Components\FileUpload::make('files')
                            ->label('Seleccionar archivos')
                            ->multiple()
                            ->disk('public')
                            ->directory('media/' . now()->format('Y/m'))
                            // Sin 'image/svg+xml': un SVG es XML y admite <script>,
                            // asi que servido desde nuestro dominio seria XSS con la
                            // sesion del administrador. MediaService ya lo rechaza al
                            // mirar el contenido; dejarlo aqui solo conseguia que el
                            // navegador aceptara la subida para que el servidor la
                            // borrara despues.
                            ->acceptedFileTypes([
                                'image/jpeg', 'image/png', 'image/gif',
                                'image/webp',
                                'application/pdf',
                                'video/mp4', 'video/webm',
                            ])
                            ->maxSize(20480)  // 20 MB
                            ->maxFiles(20)
                            ->helperText('Imágenes, PDFs y videos. Máx 20 MB por archivo. Las imágenes se comprimen automáticamente a WebP.')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('folder')
                            ->label('Carpeta (opcional)')
                            ->options([
                                'general'    => 'General',
                                'noticias'   => 'Noticias',
                                'paginas'    => 'Páginas',
                                'documentos' => 'Documentos',
                                'banners'    => 'Banners',
                                'logos'      => 'Logos',
                            ])
                            ->placeholder('Sin carpeta'),
                    ])
                    ->action(function (array $data) {
                        $count  = 0;
                        $folder = $data['folder'] ?? null;

                        foreach ((array) ($data['files'] ?? []) as $storedPath) {
                            try {
                                $media = MediaService::processFromStoredPath($storedPath);

                                if ($folder) {
                                    $media->update(['folder' => $folder]);
                                }

                                $count++;
                            } catch (\Throwable $e) {
                                \Log::warning("MediaService::processFromStoredPath failed for {$storedPath}: " . $e->getMessage());
                            }
                        }

                        Notification::make()
                            ->title("{$count} archivo(s) subido(s) correctamente")
                            ->success()
                            ->send();
                    })
                    ->modalWidth('xl')
                    ->modalSubmitActionLabel('Subir'),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Pages
    // ──────────────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMedia::route('/'),
            'edit'   => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
