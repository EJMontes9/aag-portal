<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriberResource\Pages;
use App\Models\Subscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Contenido';
    protected static ?string $navigationLabel = 'Suscriptores boletin';
    protected static ?string $modelLabel = 'suscriptor';
    protected static ?string $pluralModelLabel = 'suscriptores';
    protected static ?int $navigationSort = 11;
    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Forms\Components\TextInput::make('name')
                ->label('Nombre (opcional)')
                ->maxLength(255),
            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'pending' => 'Pendiente de confirmacion',
                    'confirmed' => 'Confirmado',
                    'unsubscribed' => 'Cancelo suscripcion',
                ])
                ->required()
                ->default('confirmed'),
            Forms\Components\TextInput::make('source')
                ->label('Origen')
                ->maxLength(100)
                ->placeholder('news_detail, sidebar, homepage'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->weight('medium')
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'success',
                        'pending' => 'warning',
                        'unsubscribed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => 'Confirmado',
                        'pending' => 'Pendiente',
                        'unsubscribed' => 'Cancelado',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('source')
                    ->label('Origen')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d M Y · H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('confirmed_at')
                    ->label('Confirmado')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendientes',
                        'confirmed' => 'Confirmados',
                        'unsubscribed' => 'Cancelados',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (Subscriber $r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->action(fn (Subscriber $r) => $r->confirm()),
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
            'index' => Pages\ListSubscribers::route('/'),
            'create' => Pages\CreateSubscriber::route('/create'),
            'edit' => Pages\EditSubscriber::route('/{record}/edit'),
        ];
    }
}
