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
class CreatePrestamos extends CreateRecord
{
    protected static string $resource = PrestamosResource::class;


    
        protected function getHeaderActions(): array
        {
            return [
                Actions\Action::make('importar')
                    ->label('Importar Excel')
                    ->action('importExcel')
                    ->form([
                        FileUpload::make('excel_file')
                            ->required()
                            ->label('Archivo Excel')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel']),
                    ]),
            ];
        }
}