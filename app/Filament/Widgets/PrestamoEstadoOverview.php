<?php

namespace App\Filament\Widgets;

use App\Models\Prestamo;
use App\Helpers\CurrencyHelper;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class PrestamoEstadoOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $prestamos = Prestamo::where('estado', 'A')->get();
        
        $totalUSD = 0;
        $saldoUSD = 0;
        $promedioUSD = 0;
        
        foreach ($prestamos as $prestamo) {
            $montoUSD = CurrencyHelper::convertToBaseCurrency($prestamo->monto_prestamo, $prestamo->moneda);
            $saldoUSD += CurrencyHelper::convertToBaseCurrency($prestamo->saldo_prestamo, $prestamo->moneda);
            $totalUSD += $montoUSD;
        }
        
        $promedioUSD = $prestamos->count() > 0 ? $totalUSD / $prestamos->count() : 0;
        $porcentajePagado = $totalUSD > 0 ? (($totalUSD - $saldoUSD) / $totalUSD) * 100 : 0;

        return [
            Stat::make('Préstamos Activos', $prestamos->count())
                ->description('Financiamientos vigentes')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray')
                ->chart([7, 3, 4, 5, 6, 3, 5])
                ->chartColor('gray'),
            
            Stat::make('Deuda Total', CurrencyHelper::formatCurrency($totalUSD, 'USD'))
                ->description('Monto total financiado')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('blue')
                ->chart([5, 3, 7, 8, 2, 9, 6])
                ->chartColor('blue'),
            
            Stat::make('Saldo Pendiente', CurrencyHelper::formatCurrency($saldoUSD, 'USD'))
                ->description('Por pagar')
                ->descriptionIcon('heroicon-m-scale')
                ->color('indigo')
                ->chart([9, 7, 6, 5, 4, 3, 2])
                ->chartColor('indigo'),
            
            Stat::make('Avance de Pago', number_format($porcentajePagado, 1) . '%')
                ->description('Del total financiado')
                ->descriptionIcon('heroicon-m-truck')
                ->color($porcentajePagado > 70 ? 'green' : 'orange')
                ->chart([1, 2, 3, 4, 5, 6, 7])
                ->chartColor($porcentajePagado > 70 ? 'green' : 'orange'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}