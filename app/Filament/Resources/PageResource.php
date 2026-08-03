<?php

namespace App\Filament\Resources;

use App\Blocks\BlockRegistry;
use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Components\Builder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Páginas';
    protected static ?string $modelLabel = 'página';
    protected static ?string $pluralModelLabel = 'páginas';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(['default' => 1, 'lg' => 12])->schema([

                // === COLUMNA IZQUIERDA: Builder de bloques ===
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Bloques de la página')
                        ->description('Agrega, reordena y edita los bloques. Los cambios se guardan al hacer clic en "Guardar cambios".')
                        ->icon('heroicon-o-squares-2x2')
                        ->schema([
                            Builder::make('blocks_builder')
                                ->label('')
                                ->addActionLabel('+ Agregar bloque')
                                ->blocks(BlockRegistry::filamentBlocks())
                                ->collapsible()
                                ->collapsed()
                                ->blockNumbers(false)
                                ->reorderable()
                                ->dehydrated(true),
                        ]),
                ])->columnSpan(['default' => 1, 'lg' => 8]),

                // === COLUMNA DERECHA: Ajustes de la pagina ===
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Ajustes de la página')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(255),
                            Forms\Components\TextInput::make('slug')->label('Slug / URL')->required()->prefix('/')->helperText('Ej: nosotros, contacto'),
                            Forms\Components\Select::make('status')
                                ->label('Estado')
                                ->options(['draft' => 'Borrador', 'published' => 'Publicada'])
                                ->default('published')
                                ->required(),
                        ]),
                    Forms\Components\Section::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Forms\Components\TextInput::make('meta_title')->label('Meta title'),
                            Forms\Components\Textarea::make('meta_description')->label('Meta description')->rows(3),
                        ])
                        ->collapsed(),
                ])->columnSpan(['default' => 1, 'lg' => 4]),

            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Página')->searchable()->sortable()
                    ->icon(fn (Page $r) => $r->key === 'home' ? 'heroicon-o-home' : 'heroicon-o-document'),
                Tables\Columns\TextColumn::make('slug')->label('URL')->prefix('/')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('blocks_count')->label('Bloques')->counts('blocks'),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'warning')
                    ->formatStateUsing(fn (string $state) => $state === 'published' ? 'Publicada' : 'Borrador'),
                Tables\Columns\TextColumn::make('updated_at')->label('Actualizada')->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('visual_editor')
                    ->label('Editor visual')
                    ->icon('heroicon-o-paint-brush')
                    ->color('success')
                    ->url(fn (Page $r) => url('/admin/visual-editor/'.$r->id)),
                Tables\Actions\Action::make('view')
                    ->label('Ver público')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Page $r) => $r->key === 'home' ? url('/') : url('/'.$r->slug))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()->label('Avanzado'),
            ])
            ->defaultSort('title');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
