<?php

namespace App\Filament\Resources\FormBuilderResource\Pages;

use App\Filament\Resources\FormBuilderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateForm extends CreateRecord
{
    protected static string $resource = FormBuilderResource::class;

    protected function getRedirectUrl(): string
    {
        // Tras crear, ir al edit para añadir campos via RelationManager
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Formulario creado — ahora agrega los campos abajo';
    }
}
