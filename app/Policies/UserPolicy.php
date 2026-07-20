<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Quien puede gestionar cuentas del panel.
 *
 * Hace falta que exista: el panel esta configurado para DENEGAR cuando un
 * recurso no tiene policy (ver AdminPanelProvider::boot), asi que sin este
 * archivo la seccion de usuarios seria inaccesible incluso para el
 * super_admin.
 */
class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_user');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('view_user');
    }

    public function create(User $user): bool
    {
        return $user->can('create_user');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('update_user');
    }

    /**
     * Nadie borra su propia cuenta, tenga el permiso que tenga: es la forma
     * mas comun de quedarse fuera del panel sin poder volver a entrar.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('delete_user');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_user');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('restore_user');
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('force_delete_user');
    }
}
