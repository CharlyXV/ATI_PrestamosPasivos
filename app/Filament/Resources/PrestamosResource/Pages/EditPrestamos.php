<?php

namespace App\Filament\Resources\PrestamosResource\Pages;

use Illuminate\Support\Facades\App;
use App\Filament\Resources\PrestamosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Http\Controllers\ReportPayController;

class EditPrestamos extends EditRecord
{
    protected static string $resource = PrestamosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reporte')
    ->label('Reporte Plan de Pagos')
    ->url(fn() => route('pay.report', ['prestamo' => $this->record->id])) // ✅ Parámetro incluido
    ->openUrlInNewTab(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Devolver los datos antes de guardar el préstamo editado
        return $data;
    }

    protected function afterSave(): void
    {
        // Actualizar el préstamo y el plan de pagos después de guardar el registro editado
        $prestamo = $this->record;

        $reportPayController = App::make(ReportPayController::class);
        $reportPayController->createPaymentPlan($prestamo);
    }
}

