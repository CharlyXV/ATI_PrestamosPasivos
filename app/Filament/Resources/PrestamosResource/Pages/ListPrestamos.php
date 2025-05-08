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

   
}
