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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['planpagos'])) {
            foreach ($data['planpagos'] as &$cuota) {
                $cuota = $this->formatPlanPagoValues($cuota, $data);
            }
        }
        
        return $data;
    }
    
    
    
    // Métodos auxiliares mejorados
    private function formatPlanPagoValues(array $cuota, array $prestamoData): array
    {
        $formatDecimal = function ($value) {
            if (is_string($value) && stripos($value, 'E') !== false) {
                return (float)sprintf('%.2f', (float)$value);
            }
            return round((float)$value, 2);
        };
        
        return [
            'saldo_prestamo' => $formatDecimal($cuota['saldo_prestamo'] ?? $prestamoData['monto_prestamo']),
            'saldo_principal' => $formatDecimal($cuota['monto_principal'] ?? 0),
            'saldo_interes' => $formatDecimal($cuota['monto_interes'] ?? 0),
            'saldo_seguro' => $formatDecimal($cuota['monto_seguro'] ?? 0),
            'saldo_otros' => $formatDecimal($cuota['monto_otros'] ?? 0),
            'tasa_interes' => $formatDecimal($prestamoData['tasa_interes']),
            'observaciones' => $cuota['observaciones'] ?? 'Cuota programada',
            'plp_estados' => $cuota['plp_estados'] ?? 'pendiente',
            // Mantener todos los campos originales
            'numero_cuota' => $cuota['numero_cuota'],
            'fecha_pago' => $cuota['fecha_pago'],
            'monto_principal' => $formatDecimal($cuota['monto_principal']),
            'monto_interes' => $formatDecimal($cuota['monto_interes']),
            'monto_seguro' => $formatDecimal($cuota['monto_seguro']),
            'monto_otros' => $formatDecimal($cuota['monto_otros']),
        ];
    }
    
    private function fixScientificNotationValues($prestamo): void
    {
        // Obtener los IDs existentes antes de la generación
        $existingIds = $prestamo->planpagos()->pluck('id');
        
        // Esperar un breve momento para que se complete la generación
        sleep(1);
        
        // Actualizar solo los nuevos registros
        $prestamo->planpagos()
            ->whereNotIn('id', $existingIds)
            ->get()
            ->each(function ($plan) {
                $plan->update([
                    'saldo_prestamo' => $this->formatDecimalForDB($plan->saldo_prestamo),
                    'saldo_principal' => $this->formatDecimalForDB($plan->saldo_principal),
                    'saldo_interes' => $this->formatDecimalForDB($plan->saldo_interes),
                    'saldo_seguro' => $this->formatDecimalForDB($plan->saldo_seguro),
                    'saldo_otros' => $this->formatDecimalForDB($plan->saldo_otros),
                    'monto_principal' => $this->formatDecimalForDB($plan->monto_principal),
                    'monto_interes' => $this->formatDecimalForDB($plan->monto_interes),
                    'monto_seguro' => $this->formatDecimalForDB($plan->monto_seguro),
                    'monto_otros' => $this->formatDecimalForDB($plan->monto_otros),
                ]);
            });
    }
    
    private function formatDecimalForDB($value): float
    {
        if (is_string($value) && stripos($value, 'E') !== false) {
            return (float)number_format((float)$value, 2, '.', '');
        }
        return round((float)$value, 2);
    }
}

