<?php

namespace App\Filament\Resources\FormBuilderResource\Pages;

use App\Filament\Resources\FormBuilderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static string $resource = FormBuilderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Formulario actualizado correctamente';
    }
}
