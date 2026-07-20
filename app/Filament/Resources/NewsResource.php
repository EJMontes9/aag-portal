<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Noticias';
    protected static ?string $modelLabel = 'noticia';
    protected static ?string $pluralModelLabel = 'noticias';
    protected static ?int $navigationSort = 5;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos basicos')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Titulo')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $set, $get, $record) {
                            if (! $record && empty($get('slug'))) {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }
                        }),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug (URL)')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Se autogenera del titulo. Solo letras, numeros y guiones.'),
                    Forms\Components\Textarea::make('excerpt')
                        ->label('Extracto')
                        ->rows(2)
                        ->maxLength(280)
                        ->helperText('Resumen breve. Aparece en listados y compartidos en redes.')
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Contenido por bloques')
                ->description('Compon la noticia agregando bloques: texto, imagenes, video, mapas, citas, etc. Arrastra para reordenar.')
                ->schema([
                    Forms\Components\Builder::make('content_blocks')
                        ->label('')
                        ->blocks(\App\NewsBlocks\NewsBlockRegistry::filamentBlocks())
                        ->blockNumbers(false)
                        ->collapsible()
                        ->collapsed()
                        ->blockPickerColumns(2)
                        ->blockPickerWidth('lg')
                        ->addActionLabel('+ Agregar bloque')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Contenido legacy (texto plano)')
                ->description('Campo opcional para texto simple sin bloques. Si tu noticia ya usa bloques arriba, deja esto vacio.')
                ->collapsed()
                ->schema([
                    Forms\Components\RichEditor::make('content')
                        ->label('')
                        ->disableToolbarButtons(['attachFiles'])
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Imagen destacada')
                ->schema([
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('Imagen de portada')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                        ->imageEditor()
                        ->directory('news/covers')
                        ->disk('public')
                        ->maxSize(4096)
                        ->helperText('JPG/PNG, max 4MB. Recomendado 1600x900.'),
                    Forms\Components\TextInput::make('cover_image_alt')
                        ->label('Texto alternativo de la imagen')
                        ->maxLength(255)
                        ->helperText('Descripcion para lectores de pantalla y SEO.'),
                ])->columns(2),

            Forms\Components\Section::make('Clasificacion y publicacion')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Categoria')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->maxLength(80),
                            Forms\Components\ColorPicker::make('color')->label('Color del badge'),
                        ]),
                    Forms\Components\Select::make('author_id')
                        ->label('Autor')
                        ->relationship('author', 'name')
                        ->searchable()
                        ->default(fn () => auth()->id()),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'draft' => 'Borrador',
                            'published' => 'Publicada',
                            'archived' => 'Archivada',
                        ])
                        ->default('draft')
                        ->required(),
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Fecha de publicacion')
                        ->seconds(false)
                        ->helperText('Si se deja vacio y el estado es "Publicada", se asigna ahora.'),
                    Forms\Components\Toggle::make('featured_on_home')
                        ->label('Destacar en home')
                        ->helperText('Aparecera en el bloque "Noticias destacadas" del home.'),
                ])->columns(2),

            Forms\Components\Section::make('SEO')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('meta_title')
                        ->label('Meta title')
                        ->maxLength(70)
                        ->helperText('Si se deja vacio, usa el titulo.'),
                    Forms\Components\Textarea::make('meta_description')
                        ->label('Meta description')
                        ->maxLength(160)
                        ->rows(2)
                        ->helperText('Si se deja vacio, usa el extracto.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->size(50),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->limit(60)
                    ->wrap()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->badge()
                    ->color(fn ($record) => $record?->category?->color ? null : 'gray')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Publicada',
                        'draft' => 'Borrador',
                        'archived' => 'Archivada',
                        default => ucfirst($state),
                    }),
                Tables\Columns\IconColumn::make('featured_on_home')
                    ->label('Home')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publicada')
                    ->dateTime('d M Y · H:i')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Vistas')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Borrador',
                        'published' => 'Publicada',
                        'archived' => 'Archivada',
                    ]),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('featured_on_home')
                    ->label('Destacada'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_public')
                    ->label('Ver en sitio')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (News $record): string => url('/noticias/'.$record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (News $record): bool => $record->status === 'published'),
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
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
