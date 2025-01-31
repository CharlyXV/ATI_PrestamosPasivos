<?php

namespace App\Filament\Resources\PrestamosResource\Pages;

use App\Filament\Resources\PrestamosResource;
use Filament\Actions;
use Filament\Actions\Action;
use App\Imports\PrestamosImport;
//use Maatwebsite\Facades\Excel;
use Maatwebsite\Excel\Facades\Excel;
use App\Filament\Resources\LoanResource;
use App\Imports\PlanpagoImport;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;

class ListPrestamos extends ListRecords
{
    protected static string $resource = PrestamosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('import')
             ->label('Importar Plan Pagos')
             ->color('danger')
             ->icon('heroicon-o-document-arrow-down')
             ->form([
                FileUpload::make('attachment'),
             ])
             ->action(function (array $data) {
             
                $file = public_path('storage/'. $data['attachment']);
               
                //dd($data);
                //dd($file);

                Excel::import(new PlanpagoImport, $file);


                Notification::make()
                ->Title('Importar Plan Pagos')
                ->success()
                ->send();
                
             })
 
        ];
    }
}
