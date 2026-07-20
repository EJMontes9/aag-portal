<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                // Nadie borra su propia cuenta desde aqui tampoco.
                ->visible(fn (User $record) => $record->id !== auth()->id()),
        ];
    }

    /**
     * SEGURIDAD -- Impide quedarse fuera del panel por accidente.
     *
     * Si un usuario se edita a si mismo y se quita todos los roles que dan
     * acceso, al guardar perderia el panel de inmediato y no habria forma de
     * recuperarlo sin entrar por consola al servidor.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->record->id !== auth()->id()) {
            return $data;
        }

        $rolesConAcceso = ['super_admin', 'admin', 'editor', 'publisher'];
        $seleccionados  = $data['roles'] ?? [];

        $nombres = \Spatie\Permission\Models\Role::whereIn('id', $seleccionados)
            ->pluck('name')
            ->all();

        if (empty(array_intersect($nombres, $rolesConAcceso))) {
            Notification::make()
                ->title('No puedes quitarte a ti mismo el acceso al panel')
                ->body('Se conservaron tus roles anteriores. Pide a otro administrador que haga el cambio si de verdad hace falta.')
                ->warning()
                ->persistent()
                ->send();

            // Se dejan los roles como estaban
            $data['roles'] = $this->record->roles->pluck('id')->all();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
