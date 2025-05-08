<?php

namespace App\Filament\Resources\AplicarReciboResource\Pages;

use App\Filament\Resources\AplicarReciboResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAplicarRecibos extends ListRecords
{
    protected static string $resource = AplicarReciboResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
