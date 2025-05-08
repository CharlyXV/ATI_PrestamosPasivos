<?php

namespace App\Filament\Widgets;

use App\Models\Prestamo;
use App\Enums\PrestamoEstadoEnum;
use Filament\Widgets\ChartWidget;

class Pchart extends ChartWidget
{
    protected static ?string $heading = 'Distribución por Cantidad';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '350px';
    
    protected int | string | array $columnSpan = [
        'default' => 12, // Full width en móviles
        'md' => 6,       // Mitad en tablets
        'lg' => 5        // Ajuste fino en desktop
    ];
    
    protected function getData(): array
    {
        $data = Prestamo::select('estado')
            ->selectRaw('count(*) as count')
            ->groupBy('estado')
            ->get();
            
        $colors = [
            'A' => ['bg' => 'rgba(59, 130, 246, 0.8)', 'border' => 'rgba(37, 99, 235, 1)'],
            'L' => ['bg' => 'rgba(16, 185, 129, 0.8)', 'border' => 'rgba(5, 150, 105, 1)'],
            'I' => ['bg' => 'rgba(100, 116, 139, 0.8)', 'border' => 'rgba(71, 85, 105, 1)'],
        ];
        
        $labels = ['Vigentes', 'Liquidados', 'En Proceso'];
        $values = [];
        $backgroundColors = [];
        
        foreach (PrestamoEstadoEnum::cases() as $case) {
            $record = $data->firstWhere('estado', $case->value);
            $values[] = $record ? $record->count : 0;
            $backgroundColors[] = $colors[$case->value]['bg'];
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Cantidad',
                    'data' => $values,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => array_column($colors, 'border'),
                    'borderWidth' => 1,
                    'barThickness' => 40, // Controla el grosor de las barras
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1
                    ],
                    'grid' => [
                        'drawOnChartArea' => true,
                        'color' => 'rgba(0, 0, 0, 0.05)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false, // Ocultamos leyenda para ahorrar espacio
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => 'rgba(30, 41, 59, 0.95)',
                    'bodyFont' => ['size' => 14],
                    'callbacks' => [
                        'label' => 'function(context) {
                            return ` ${context.label}: ${context.raw} financiamientos`;
                        }'
                    ]
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
