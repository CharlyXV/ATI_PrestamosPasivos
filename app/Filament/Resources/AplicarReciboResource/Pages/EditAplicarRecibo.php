<?php

namespace App\Filament\Resources\AplicarReciboResource\Pages;

use App\Filament\Resources\AplicarReciboResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAplicarRecibo extends EditRecord
{
    protected static string $resource = AplicarReciboResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
