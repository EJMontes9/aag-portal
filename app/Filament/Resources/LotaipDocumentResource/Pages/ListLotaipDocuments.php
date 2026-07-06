<?php

namespace App\Filament\Resources\LotaipDocumentResource\Pages;

use App\Filament\Resources\LotaipDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLotaipDocuments extends ListRecords
{
    protected static string $resource = LotaipDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Subir documento')];
    }
}
