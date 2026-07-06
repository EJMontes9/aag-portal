<?php

namespace App\Filament\Resources\LotaipYearResource\Pages;

use App\Filament\Resources\LotaipYearResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLotaipYears extends ListRecords
{
    protected static string $resource = LotaipYearResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nuevo año')];
    }
}
