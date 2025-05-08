<?php

namespace App\Filament\Resources\ReporteDisponibilidadResource\Pages;

use App\Filament\Resources\ReporteDisponibilidadResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageReporteDisponibilidads extends ManageRecords
{
    protected static string $resource = ReporteDisponibilidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
