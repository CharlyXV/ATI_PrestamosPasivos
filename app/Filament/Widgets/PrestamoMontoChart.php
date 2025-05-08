<?php

namespace App\Filament\Widgets;

use App\Models\Prestamo;
use App\Helpers\CurrencyHelper;
use Filament\Widgets\ChartWidget;

class PrestamoMontoChart extends ChartWidget
{
    protected static ?string $heading = 'Distribución por Monto (USD)';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '350px';
    
    protected int | string | array $columnSpan = [
        'default' => 12, // Full width en móviles
        'md' => 6,       // Mitad en tablets
        'lg' => 5        // Ajuste fino en desktop
    ];
    
    protected function getData(): array
    {
        $prestamos = Prestamo::all();
        $dataByEstado = ['A' => 0, 'L' => 0, 'I' => 0];
        
        foreach ($prestamos as $prestamo) {
            $amountUSD = CurrencyHelper::convertToBaseCurrency($prestamo->monto_prestamo, $prestamo->moneda);
            $dataByEstado[$prestamo->estado] += $amountUSD;
        }
        
        $colors = [
            'A' => ['bg' => 'rgba(59, 130, 246, 0.8)', 'border' => 'rgba(37, 99, 235, 1)'],
            'L' => ['bg' => 'rgba(16, 185, 129, 0.8)', 'border' => 'rgba(5, 150, 105, 1)'],
            'I' => ['bg' => 'rgba(100, 116, 139, 0.8)', 'border' => 'rgba(71, 85, 105, 1)'],
        ];
        
        $labels = ['Vigentes', 'Liquidados', 'En Proceso'];
        $values = [];
        $backgroundColors = [];
        
        foreach ($dataByEstado as $estado => $amount) {
            $values[] = round($amount, 2);
            $backgroundColors[] = $colors[$estado]['bg'];
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Monto USD',
                    'data' => $values,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => array_column($colors, 'border'),
                    'borderWidth' => 1,
                    'barThickness' => 40,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Hacemos las barras horizontales
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "$" + value.toLocaleString(); }'
                    ],
                    'grid' => [
                        'drawOnChartArea' => true,
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => 'function(context) {
                            return ` ${context.label}: $${context.raw.toLocaleString()}`;
                        }'
                    ]
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}