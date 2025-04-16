<?php

namespace App\Filament\Resources\PrestamosResource\Pages;

use App\Filament\Resources\PrestamosResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Tables\Actions\Action; 
use Filament\Tables\Columns\TextColumn; 
use Maatwebsite\Excel\Facades\Excel; 
use App\Imports\PrestamosImport;
use App\Models\Planpago;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use App\Http\Controllers\ReportPayController;

class CreatePrestamos extends CreateRecord
{
    protected static string $resource = PrestamosResource::class;

    // Método que se ejecuta después de crear un registro
    protected function afterCreate(): void
    {
        $reportPayController = app(\App\Http\Controllers\ReportPayController::class);
        $reportPayController->createPaymentPlan($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importar')
                ->label('Importar Excel')
                ->action(function (array $data) {
                    // Lógica para importar Excel
                    try {
                        Excel::import(new PrestamosImport, $data['excel_file']);
                        Notification::make()
                            ->title('Importación exitosa')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error en la importación')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->form([
                    FileUpload::make('excel_file')
                        ->required()
                        ->label('Archivo Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']),
                ]),
        ];
    }
}