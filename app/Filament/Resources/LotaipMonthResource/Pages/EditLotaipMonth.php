<?php

namespace App\Filament\Resources\LotaipMonthResource\Pages;

use App\Filament\Resources\LotaipMonthResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLotaipMonth extends EditRecord
{
    protected static string $resource = LotaipMonthResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
