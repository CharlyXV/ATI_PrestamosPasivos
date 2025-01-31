<?php

namespace App\Filament\Resources\RegistroDePagosResource\Pages;

use App\Filament\Resources\RegistroDePagosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegistroDePagos extends ListRecords
{
    protected static string $resource = RegistroDePagosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
