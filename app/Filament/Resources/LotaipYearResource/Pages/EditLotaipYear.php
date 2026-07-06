<?php

namespace App\Filament\Resources\LotaipYearResource\Pages;

use App\Filament\Resources\LotaipYearResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLotaipYear extends EditRecord
{
    protected static string $resource = LotaipYearResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
