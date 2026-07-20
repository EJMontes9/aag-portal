<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Gestion de usuarios del panel.
 *
 * No existia: las cuentas solo se podian crear desde el seeder y los roles
 * solo se podian asignar por consola, de modo que no habia forma de dar
 * acceso a alguien nuevo sin tocar el servidor.
 *
 * SEGURIDAD -- Dos reglas que se aplican aqui y conviene no perder de vista:
 *
 *   1. Solo un super_admin puede otorgar el rol super_admin. Si no, cualquier
 *      administrador podria auto-ascenderse o crear una cuenta con todos los
 *      permisos, y la separacion de roles dejaria de servir para nada.
 *
 *   2. Nadie puede borrar su propia cuenta ni quitarse a si mismo el ultimo
 *      rol con acceso: es la forma tipica de quedarse fuera del panel sin
 *      poder volver a entrar.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Usuarios y Roles';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $modelLabel = 'usuario';
    protected static ?string $pluralModelLabel = 'usuarios';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de la cuenta')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Con este correo iniciará sesión.'),

                    Forms\Components\TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->maxLength(255)
                        // Obligatoria al crear; al editar, vacia = no cambiarla.
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->helperText(fn (string $operation) => $operation === 'edit'
                            ? 'Déjala vacía para no cambiarla.'
                            : 'Mínimo 12 caracteres. Comunícasela por un medio seguro.')
                        ->minLength(12)
                        ->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Permisos')
                ->description('El rol determina a qué secciones del panel puede entrar y qué puede hacer en ellas.')
                ->schema([
                    Forms\Components\CheckboxList::make('roles')
                        ->label('Roles')
                        ->relationship('roles', 'name')
                        // Un administrador no puede otorgar super_admin: solo
                        // quien ya lo es. Evita la auto-escalada de privilegios.
                        ->options(function () {
                            $query = Role::query();

                            if (! auth()->user()?->hasRole('super_admin')) {
                                $query->where('name', '!=', 'super_admin');
                            }

                            return $query->pluck('name', 'id');
                        })
                        ->descriptions(self::descripcionesDeRoles())
                        ->required()
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Explicacion de cada rol en el propio formulario, para no tener que
     * consultar la documentacion al dar de alta a alguien.
     */
    protected static function descripcionesDeRoles(): array
    {
        $textos = [
            'super_admin'   => 'Acceso total, incluida la gestión de usuarios y roles.',
            'admin'         => 'Todo el contenido y la configuración. No gestiona roles.',
            'publisher'     => 'Crea, edita y publica contenido. Sin datos personales ni configuración.',
            'editor'        => 'Redacta y edita contenido. No puede eliminar ni ver datos personales.',
            'transparencia' => 'Solo la sección de Transparencia: LOTAIP y Rendición de Cuentas.',
        ];

        return Role::pluck('name', 'id')
            ->map(fn ($nombre) => $textos[$nombre] ?? '')
            ->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->color(fn ($state) => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Alta')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label('Rol')
                    ->relationship('roles', 'name')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    // Nadie borra su propia cuenta: es la forma tipica de
                    // quedarse fuera del panel sin poder volver a entrar.
                    ->visible(fn (User $record) => $record->id !== auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            // Se filtra al propio usuario tambien en el borrado
                            // masivo, donde es facil incluirse sin darse cuenta.
                            $records->reject(fn (User $u) => $u->id === auth()->id())
                                ->each->delete();
                        }),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('roles');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
