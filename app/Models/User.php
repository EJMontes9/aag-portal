<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    // HasApiTokens es lo que permite emitir tokens de la API pública desde el
    // panel (ver ApiTokenResource). Solo añade una relación y createToken(); no
    // abre acceso a nada por sí mismo: mientras API_ENABLED sea false las rutas
    // de la API ni siquiera existen.
    use HasFactory, Notifiable, HasRoles, LogsActivity, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => 'Usuario creado',
                'updated' => 'Usuario actualizado',
                'deleted' => 'Usuario eliminado',
                default   => $e,
            });
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Roles que pueden entrar al panel.
     *
     * Esto solo abre la puerta; QUÉ puede hacer cada uno dentro lo deciden los
     * permisos (ver RolePermissionSeeder). Un usuario sin ninguno de estos
     * roles no llega ni a la pantalla de inicio del panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole([
            'super_admin',
            'admin',
            'editor',
            'publisher',
            'transparencia',
        ]);
    }
}
