<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Preguntas frecuentes';
    protected static ?string $modelLabel = 'pregunta';
    protected static ?string $pluralModelLabel = 'preguntas frecuentes';
    protected static ?int $navigationSort = 7;
    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pregunta y respuesta')
                ->schema([
                    Forms\Components\TextInput::make('question')
                        ->label('Pregunta')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\RichEditor::make('answer')
                        ->label('Respuesta')
                        ->required()
                        ->disableToolbarButtons(['attachFiles'])
                        ->columnSpanFull(),
                ]),
            Forms\Components\Section::make('Clasificación')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required()->maxLength(80),
                            Forms\Components\TextInput::make('icon')->label('Heroicon (opcional)')->placeholder('heroicon-o-plane'),
                        ]),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Orden')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Activa')
                        ->default(true),
                    Forms\Components\Toggle::make('featured')
                        ->label('Destacada')
                        ->helperText('Aparecerá en bloques "FAQ destacadas" en la home u otras páginas.'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->label('Pregunta')
                    ->searchable()
                    ->wrap()
                    ->limit(80)
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                Tables\Columns\IconColumn::make('featured')
                    ->label('Destacada')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('featured'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
