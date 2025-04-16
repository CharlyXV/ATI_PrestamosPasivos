<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class Recibo extends Model
{
    protected $table = 'recibos';

    protected $fillable = [
        'numero_recibo',
        'tipo_recibo',
        'detalle',
        'estado',
        'banco_id',
        'cuenta_desembolso',
        'monto_recibo',
        'fecha_pago',
        'fecha_deposito',
        'prestamo_id'
    ];

    protected $attributes = [
        'estado' => 'I' // Valor por defecto
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'fecha_deposito' => 'date',
        'monto_recibo' => 'decimal:2'
    ];

    // Relación con banco
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }

    // Relación con préstamo
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    // Relación con detalles
    public function detalles()
    {
        return $this->hasMany(DetalleRecibo::class);
    }

    public function completar()
    {
        DB::transaction(function () {
            // Marcar recibo como completado
            $this->update(['estado' => 'C']);

            // Actualizar cada cuota asociada
            foreach ($this->detalles as $detalle) {
                if ($planPago = $detalle->planpago) {
                    $planPago->update([
                        'plp_estados' => 'completado',
                        'fecha_pago_real' => $this->fecha_pago
                    ]);

                    // Actualizar saldo del préstamo
                    $prestamo = $this->prestamo;
                    $prestamo->saldo_prestamo -= $detalle->monto_principal;
                    $prestamo->save();
                }
            }
        });
    }

    // Procesar pago
    public function procesarPago()
    {
        DB::transaction(function () {
            // Validaciones
            if ($this->estado !== 'I') {
                throw new \Exception('Solo se pueden procesar recibos en estado Incluido');
            }

            foreach ($this->detalles as $detalle) {
                $planPago = $detalle->planpago;

                // Validar montos contra saldos
                if (
                    $detalle->monto_principal > $planPago->saldo_principal ||
                    $detalle->monto_intereses > $planPago->saldo_interes ||
                    $detalle->monto_seguro > $planPago->saldo_seguro ||
                    $detalle->monto_otros > $planPago->saldo_otros
                ) {
                    // Muestra notificación de error
                    Notification::make()
                        ->title('Error en cuota #' . $detalle->numero_cuota)
                        ->body('Los montos ingresados exceden los saldos disponibles.')
                        ->danger()
                        ->persistent() // Opcional: Permite cerrar manualmente
                        ->send();

                    // Detiene el proceso (similar al throw)
                    return; // o `return null;` dependiendo del contexto
                }
            }

            // Actualizar saldos
            foreach ($this->detalles as $detalle) {
                $planPago = $detalle->planpago;

                $planPago->update([
                    'saldo_principal' => $planPago->saldo_principal - $detalle->monto_principal,
                    'saldo_interes' => $planPago->saldo_interes - $detalle->monto_intereses,
                    'saldo_seguro' => $planPago->saldo_seguro - $detalle->monto_seguro,
                    'saldo_otros' => $planPago->saldo_otros - $detalle->monto_otros,
                    'plp_estados' => ($planPago->saldo_principal <= 0 &&
                        $planPago->saldo_interes <= 0 &&
                        $planPago->saldo_seguro <= 0 &&
                        $planPago->saldo_otros <= 0) ? 'completado' : 'pendiente'
                ]);
            }

            // Actualizar préstamo
            $this->prestamo->update([
                'saldo_prestamo' => $this->prestamo->saldo_prestamo - $this->detalles->sum('monto_principal'),
                'proximo_pago' => $this->prestamo->planpagos()
                    ->where('plp_estados', 'pendiente')
                    ->orderBy('fecha_pago')
                    ->first()?->fecha_pago
            ]);

            // Cambiar estado a Contabilizado
            $this->update(['estado' => 'C']);
        });
    }

    // app/Models/Recibo.php
    public function anular()
    {
        DB::transaction(function () {
            // Marcar recibo como anulado
            $this->update(['estado' => 'A']);

            // Revertir cuotas si estaban marcadas como completadas
            foreach ($this->detalles as $detalle) {
                if ($planPago = $detalle->planpago) {
                    $planPago->update([
                        'plp_estados' => 'pendiente',
                        'fecha_pago_real' => null
                    ]);

                    // Revertir saldo del préstamo
                    $prestamo = $this->prestamo;
                    $prestamo->saldo_prestamo += $detalle->monto_principal;
                    $prestamo->save();
                }
            }
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->numero_recibo = $model->numero_recibo ?? 'REC-' . now()->format('YmdHis');
            $model->estado = $model->estado ?? 'I';
        });
    }

    public function canAnular()
    {
        return $this->estado === 'I'; // Solo se puede anular si está Incluido
    }

    public function canCompletar()
    {
        // Verificar que todas las cuotas asociadas existan y estén pendientes
        return $this->estado === 'I' &&
            $this->detalles->every(function ($detalle) {
                return $detalle->planpago &&
                    $detalle->planpago->plp_estados === 'pendiente';
            });
    }
}

// En el modelo Recibo
