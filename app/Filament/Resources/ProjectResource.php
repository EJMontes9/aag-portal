<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Proyectos y obras';
    protected static ?string $modelLabel = 'proyecto';
    protected static ?string $pluralModelLabel = 'proyectos y obras';
    protected static ?int $navigationSort = 9;
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
                        ->helperText('Se autogenera del titulo.'),
                    Forms\Components\Textarea::make('summary')
                        ->label('Resumen corto')
                        ->rows(2)
                        ->maxLength(280)
                        ->helperText('Aparece en cards del listado.')
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('description')
                        ->label('Descripcion completa')
                        ->disableToolbarButtons(['attachFiles'])
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Imagen principal y galeria')
                ->schema([
                    Forms\Components\FileUpload::make('cover_image')
                        ->label('Imagen de portada')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                        ->imageEditor()
                        ->directory('projects/covers')
                        ->disk('public')
                        ->maxSize(4096)
                        ->helperText('JPG/PNG, max 4MB. Recomendado 1600x900.'),
                    Forms\Components\FileUpload::make('gallery')
                        ->label('Galeria de fotos')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                        ->imageEditor()
                        ->multiple()
                        ->reorderable()
                        ->directory('projects/gallery')
                        ->disk('public')
                        ->maxSize(4096)
                        ->maxFiles(20)
                        ->helperText('Hasta 20 imagenes. Se mostraran en lightbox.'),
                ])->columns(2),

            Forms\Components\Section::make('Estado y cronograma')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('Estado del proyecto')
                        ->options([
                            'planificado' => 'Planificado',
                            'en_curso' => 'En curso',
                            'completado' => 'Completado',
                        ])
                        ->required()
                        ->default('planificado'),
                    Forms\Components\TextInput::make('budget')
                        ->label('Presupuesto')
                        ->maxLength(100)
                        ->placeholder('Ej: USD 2.5M')
                        ->helperText('Texto libre, no se procesa como numero.'),
                    Forms\Components\DatePicker::make('start_date')->label('Fecha de inicio'),
                    Forms\Components\DatePicker::make('end_date')->label('Fecha de finalizacion'),
                    Forms\Components\TextInput::make('location')
                        ->label('Ubicacion')
                        ->maxLength(150)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Hitos / Milestones')
                ->collapsed()
                ->schema([
                    Forms\Components\Repeater::make('milestones')
                        ->label('Hitos del proyecto')
                        ->schema([
                            Forms\Components\DatePicker::make('date')->label('Fecha'),
                            Forms\Components\TextInput::make('label')->label('Hito')->required()->maxLength(120),
                            Forms\Components\Toggle::make('completed')->label('Completado'),
                        ])
                        ->columns(3)
                        ->reorderable()
                        ->collapsible()
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Hito')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Publicacion y SEO')
                ->collapsed()
                ->schema([
                    Forms\Components\Toggle::make('is_published')->label('Publicado')->default(true),
                    Forms\Components\TextInput::make('sort_order')->label('Orden')->numeric()->default(0),
                    Forms\Components\TextInput::make('meta_title')->label('Meta title')->maxLength(70),
                    Forms\Components\Textarea::make('meta_description')->label('Meta description')->maxLength(160)->rows(2),
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
                    ->label('Proyecto')
                    ->searchable()
                    ->limit(60)
                    ->wrap()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'en_curso' => 'success',
                        'completado' => 'info',
                        'planificado' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'planificado' => 'Planificado',
                        'en_curso' => 'En curso',
                        'completado' => 'Completado',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('location')
                    ->label('Ubicacion')
                    ->placeholder('—')
                    ->limit(30),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Finalizacion')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Publicado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planificado' => 'Planificado',
                        'en_curso' => 'En curso',
                        'completado' => 'Completado',
                    ]),
                Tables\Filters\TernaryFilter::make('is_published'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_public')
                    ->label('Ver en sitio')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Project $r): string => url('/proyectos/'.$r->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (Project $r): bool => $r->is_published),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
