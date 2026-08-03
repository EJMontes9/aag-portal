<?php

namespace App\NewsBlocks;

use Filament\Forms;
use Filament\Forms\Components\Builder\Block;

/**
 * Bloques de contenido para el cuerpo de una Noticia.
 * Independiente del BlockRegistry del editor visual del home (otra cosa).
 *
 * Cada bloque define:
 *   - key()   identificador único
 *   - block() schema Filament Builder
 *   - view()  Blade para renderizar en frontend
 */
class NewsBlockRegistry
{
    public static function map(): array
    {
        return [
            'text' => [
                'label' => 'Texto enriquecido',
                'icon' => 'heroicon-o-document-text',
                'view' => 'news-blocks.text',
                'schema' => fn () => [
                    Forms\Components\RichEditor::make('content')
                        ->label('')
                        ->required()
                        ->disableToolbarButtons(['attachFiles']),
                ],
            ],

            'image' => [
                'label' => 'Imagen',
                'icon' => 'heroicon-o-photo',
                'view' => 'news-blocks.image',
                'schema' => fn () => [
                    Forms\Components\FileUpload::make('image')
                        ->label('Imagen')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                        ->imageEditor()
                        ->directory('news/blocks')
                        ->disk('public')
                        ->maxSize(4096)
                        ->required(),
                    Forms\Components\TextInput::make('alt')
                        ->label('Texto alternativo (accesibilidad)')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('caption')
                        ->label('Pie de foto (opcional)')
                        ->maxLength(280),
                    Forms\Components\Select::make('width')
                        ->label('Ancho')
                        ->options([
                            'content' => 'Normal (ancho del texto)',
                            'wide' => 'Ancho extendido',
                            'full' => 'Pantalla completa',
                        ])
                        ->default('content'),
                ],
            ],

            'gallery' => [
                'label' => 'Galería de imágenes',
                'icon' => 'heroicon-o-rectangle-stack',
                'view' => 'news-blocks.gallery',
                'schema' => fn () => [
                    Forms\Components\Repeater::make('images')
                        ->label('Imágenes')
                        ->schema([
                            Forms\Components\FileUpload::make('image')
                                ->label('Imagen')
                                ->image()
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                ->imageEditor()
                                ->directory('news/gallery')
                                ->disk('public')
                                ->maxSize(4096)
                                ->required(),
                            Forms\Components\TextInput::make('alt')
                                ->label('Texto alternativo')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('caption')
                                ->label('Pie de foto')
                                ->maxLength(200),
                        ])
                        ->columns(3)
                        ->minItems(2)
                        ->maxItems(20)
                        ->defaultItems(3)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['caption'] ?? $state['alt'] ?? 'Imagen'),
                    Forms\Components\Select::make('columns')
                        ->label('Columnas')
                        ->options([2 => '2 columnas', 3 => '3 columnas', 4 => '4 columnas'])
                        ->default(3),
                ],
            ],

            'video_embed' => [
                'label' => 'Video YouTube / Vimeo',
                'icon' => 'heroicon-o-play-circle',
                'view' => 'news-blocks.video-embed',
                'schema' => fn () => [
                    Forms\Components\TextInput::make('url')
                        ->label('URL del video')
                        ->placeholder('https://www.youtube.com/watch?v=... o https://vimeo.com/...')
                        ->required()
                        ->url(),
                    Forms\Components\TextInput::make('caption')
                        ->label('Descripción (opcional)')
                        ->maxLength(280),
                ],
            ],

            'video_upload' => [
                'label' => 'Video subido (MP4)',
                'icon' => 'heroicon-o-film',
                'view' => 'news-blocks.video-upload',
                'schema' => fn () => [
                    Forms\Components\FileUpload::make('video')
                        ->label('Archivo de video')
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                        ->directory('news/videos')
                        ->disk('public')
                        ->maxSize(102400) // 100 MB
                        ->required()
                        ->helperText('Máximo 100 MB. Formatos: MP4, WebM, Ogg.'),
                    Forms\Components\FileUpload::make('poster')
                        ->label('Imagen de portada (opcional)')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                        ->maxSize(4096)
                        ->directory('news/videos/posters')
                        ->disk('public'),
                    Forms\Components\TextInput::make('caption')
                        ->label('Descripción (opcional)')
                        ->maxLength(280),
                ],
            ],

            'map' => [
                'label' => 'Mapa (Google Maps)',
                'icon' => 'heroicon-o-map',
                'view' => 'news-blocks.map',
                'schema' => fn () => [
                    Forms\Components\Textarea::make('embed_url')
                        ->label('URL de embed o código iframe')
                        ->rows(3)
                        ->required()
                        ->helperText('Pega la URL "src" del iframe de Google Maps (Compartir > Insertar mapa) o el código iframe completo.'),
                    Forms\Components\TextInput::make('caption')
                        ->label('Descripción del lugar (opcional)')
                        ->maxLength(280),
                    Forms\Components\Select::make('height')
                        ->label('Altura')
                        ->options([
                            'sm' => 'Pequeña (300px)',
                            'md' => 'Mediana (450px)',
                            'lg' => 'Grande (600px)',
                        ])
                        ->default('md'),
                ],
            ],

            'quote' => [
                'label' => 'Cita destacada',
                'icon' => 'heroicon-o-chat-bubble-bottom-center-text',
                'view' => 'news-blocks.quote',
                'schema' => fn () => [
                    Forms\Components\Textarea::make('text')
                        ->label('Cita')
                        ->required()
                        ->rows(3)
                        ->maxLength(500),
                    Forms\Components\TextInput::make('author')
                        ->label('Autor (opcional)')
                        ->maxLength(120),
                    Forms\Components\TextInput::make('source')
                        ->label('Cargo o fuente (opcional)')
                        ->maxLength(120),
                ],
            ],

            'download' => [
                'label' => 'Descarga (PDF / documento)',
                'icon' => 'heroicon-o-arrow-down-tray',
                'view' => 'news-blocks.download',
                'schema' => fn () => [
                    Forms\Components\FileUpload::make('file')
                        ->label('Archivo')
                        // Bloque "Descarga (PDF / documento)": admite tambien
                        // ofimatica, no solo PDF puro.
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->directory('news/downloads')
                        ->disk('public')
                        ->maxSize(20480) // 20 MB
                        ->required(),
                    Forms\Components\TextInput::make('label')
                        ->label('Título del documento')
                        ->required()
                        ->maxLength(120),
                    Forms\Components\Textarea::make('description')
                        ->label('Descripción (opcional)')
                        ->rows(2)
                        ->maxLength(240),
                ],
            ],

            'separator' => [
                'label' => 'Separador',
                'icon' => 'heroicon-o-minus',
                'view' => 'news-blocks.separator',
                'schema' => fn () => [
                    Forms\Components\Select::make('style')
                        ->label('Estilo')
                        ->options([
                            'line' => 'Línea simple',
                            'dots' => 'Tres puntos',
                            'space' => 'Solo espacio',
                        ])
                        ->default('line'),
                ],
            ],

            // === BLOQUES DE SIDEBAR ===
            // Se muestran en la columna derecha si la noticia tiene alguno;
            // si no hay ninguno, el cuerpo ocupa el ancho completo.
            'info_card' => [
                'label' => 'Ficha informativa (sidebar)',
                'icon' => 'heroicon-o-clipboard-document-list',
                'view' => 'news-blocks.info-card',
                'sidebar' => true,
                'schema' => fn () => [
                    Forms\Components\TextInput::make('kicker')
                        ->label('Etiqueta (kicker)')
                        ->default('FICHA INFORMATIVA')
                        ->maxLength(60),
                    Forms\Components\Repeater::make('items')
                        ->label('Datos')
                        ->schema([
                            Forms\Components\TextInput::make('label')
                                ->label('Etiqueta')
                                ->required()
                                ->maxLength(80)
                                ->placeholder('Inversión estimada'),
                            Forms\Components\TextInput::make('value')
                                ->label('Valor')
                                ->required()
                                ->maxLength(120)
                                ->placeholder('USD 8.4 M'),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->maxItems(10)
                        ->defaultItems(4)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => ($state['label'] ?? '').' · '.($state['value'] ?? '')),
                ],
            ],

            'newsletter_card' => [
                'label' => 'Suscripción al boletín (sidebar)',
                'icon' => 'heroicon-o-envelope',
                'view' => 'news-blocks.newsletter-card',
                'sidebar' => true,
                'schema' => fn () => [
                    Forms\Components\TextInput::make('kicker')
                        ->label('Etiqueta')
                        ->default('BOLETÍN')
                        ->maxLength(60),
                    Forms\Components\TextInput::make('title')
                        ->label('Título')
                        ->default('Recibe nuestras noticias')
                        ->maxLength(120),
                    Forms\Components\Textarea::make('subtitle')
                        ->label('Descripción')
                        ->rows(2)
                        ->maxLength(280)
                        ->default('Cada miércoles, una selección curada de las noticias del aeropuerto y la AAG.'),
                    Forms\Components\TextInput::make('button_label')
                        ->label('Texto del boton')
                        ->default('Suscribirme')
                        ->maxLength(40),
                    Forms\Components\TextInput::make('placeholder')
                        ->label('Placeholder del email')
                        ->default('tu@correo.com')
                        ->maxLength(60),
                ],
            ],
        ];
    }

    /** Identifica si un bloque va a la sidebar. */
    public static function isSidebar(string $key): bool
    {
        return (bool) (self::map()[$key]['sidebar'] ?? false);
    }

    /** Bloques tal como los espera Filament Builder. */
    public static function filamentBlocks(): array
    {
        return collect(self::map())->map(function ($cfg, $key) {
            return Block::make($key)
                ->label($cfg['label'])
                ->icon($cfg['icon'])
                ->schema(($cfg['schema'])());
        })->values()->all();
    }

    public static function viewFor(string $key): ?string
    {
        return self::map()[$key]['view'] ?? null;
    }
}
