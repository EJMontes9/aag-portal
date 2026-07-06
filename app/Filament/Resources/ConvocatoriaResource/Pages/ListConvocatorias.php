<?php

namespace App\Filament\Resources\ConvocatoriaResource\Pages;

use App\Filament\Resources\ConvocatoriaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConvocatorias extends ListRecords
{
    protected static string $resource = ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
