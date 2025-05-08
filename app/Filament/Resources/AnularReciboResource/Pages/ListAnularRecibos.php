<?php

namespace App\Filament\Resources\AnularReciboResource\Pages;

use App\Filament\Resources\AnularReciboResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnularRecibos extends ListRecords
{
    protected static string $resource = AnularReciboResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
