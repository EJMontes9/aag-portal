<?php

namespace App\Filament\Resources\LotaipMonthResource\Pages;

use App\Filament\Resources\LotaipMonthResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLotaipMonths extends ListRecords
{
    protected static string $resource = LotaipMonthResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nuevo mes')];
    }
}
