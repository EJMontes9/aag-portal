<?php

namespace App\Filament\Resources\LotaipDocumentResource\Pages;

use App\Filament\Resources\LotaipDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLotaipDocument extends EditRecord
{
    protected static string $resource = LotaipDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
