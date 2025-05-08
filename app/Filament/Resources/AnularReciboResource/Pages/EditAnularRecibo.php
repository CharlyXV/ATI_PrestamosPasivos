<?php

namespace App\Filament\Resources\AnularReciboResource\Pages;

use App\Filament\Resources\AnularReciboResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnularRecibo extends EditRecord
{
    protected static string $resource = AnularReciboResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
