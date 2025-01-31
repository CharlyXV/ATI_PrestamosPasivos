<?php

namespace App\Filament\Widgets;

use App\Models\Prestamo;
use App\Enums\PrestamoEstadoEnum;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class Pchart extends ChartWidget
{
    protected static ?string $heading = 'Chart';
    protected static ?int $sort = 3;    
    protected function getData(): array
    {
        $data = Prestamo::select ('estado', DB::raw('count(*) as solicitado'))
                ->groupBy('estado')
                ->pluck('solicitado','estado' )
                ->toArray();
        return [ 
            'datasets' => [
                [
                   'label' => 'Prestamos Solicitados',
                   'data'  => array_values($data)
                ]
                ],      
                'labels' => PrestamoEstadoEnum::cases()
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
