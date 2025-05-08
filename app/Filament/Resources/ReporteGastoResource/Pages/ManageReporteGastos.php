<?php

namespace App\Filament\Resources\ReporteGastoResource\Pages;

use App\Filament\Resources\ReporteGastoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageReporteGastos extends ManageRecords
{
    protected static string $resource = ReporteGastoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
