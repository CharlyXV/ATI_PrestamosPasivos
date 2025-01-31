<?php

namespace App\Filament\Widgets;

use App\Models\Prestamo;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;

class PrestamoEstadoOverview extends BaseWidget
{
    
    protected function getStats(): array
    {
      
        return [
            Stat::make('Cantidad Prestamos', Prestamo::query()->where('estado','A')->count()),
            Stat::make('Monto Solicitado ', Prestamo::query()->where('estado','A')->sum('monto_prestamo')),
            Stat::make('Saldo Adeudado ', Prestamo::query()->where('estado','A')->sum('saldo_prestamo')),
            //Stat::make('Saldos Disponibles ', Prestamo::query()->where('estado','A')->sum(' monto_prestamo - saldo_prestamo ')),
            //Stat::make('Rabtis', Patient::query()->where('type','rabbit')->count()),
        ];
    }
  }
 
