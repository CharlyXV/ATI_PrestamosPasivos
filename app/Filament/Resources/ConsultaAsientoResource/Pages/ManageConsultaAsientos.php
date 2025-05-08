<?php

namespace App\Filament\Resources\ConsultaAsientoResource\Pages;

use App\Filament\Resources\ConsultaAsientoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageConsultaAsientos extends ManageRecords
{
    protected static string $resource = ConsultaAsientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
