<?php

namespace App\Filament\Resources\TipoTasaResource\Pages;

use App\Filament\Resources\TipoTasaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoTasas extends ListRecords
{
    protected static string $resource = TipoTasaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
