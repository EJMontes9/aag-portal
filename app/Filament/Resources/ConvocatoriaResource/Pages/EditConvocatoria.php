<?php

namespace App\Filament\Resources\ConvocatoriaResource\Pages;

use App\Filament\Resources\ConvocatoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConvocatoria extends EditRecord
{
    protected static string $resource = ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
