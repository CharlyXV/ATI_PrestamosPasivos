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

    public static function puedeCrearRecibo($planpagoId, $tipoPago)
    {
        if ($tipoPago === 'normal') {
            return !self::whereHas('detalles', function ($q) use ($planpagoId) {
                $q->where('planpago_id', $planpagoId);
            })
                ->where('tipo_pago', 'normal')
                ->where('estado', '!=', 'A') // Excluir anulados
                ->exists();
        }
        return true; // Para pagos parciales siempre permite
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

    public function procesarPago()
    {
        DB::transaction(function () {
            // Validar montos antes de procesar
            foreach ($this->detalles as $detalle) {
                $planPago = $detalle->planpago;
                
                if (!$planPago) {
                    throw new \Exception("La cuota #{$detalle->numero_cuota} no existe");
                }
    
                // Guardar montos originales
                $detalle->update([
                    'monto_principal_original' => $planPago->saldo_principal,
                    'monto_intereses_original' => $planPago->saldo_interes,
                    'monto_cuota' => $planPago->monto_total
                ]);
            }
    
            // Calcular el total a rebajar del saldo del préstamo
            $totalRebajar = 0;
    
            foreach ($this->detalles as $detalle) {
                $planPago = $detalle->planpago;
    
                if ($this->tipo_pago === 'normal') {
                    // Pago normal: rebajar el monto_total completo
                    $montoRebajar = $planPago->monto_total;
                    
                    // Actualizar saldos de la cuota (marcar como pagado completamente)
                    $planPago->update([
                        'saldo_principal' => 0,
                        'saldo_interes' => 0,
                        'plp_estados' => 'completado',
                        'fecha_pago_real' => $this->fecha_pago
                    ]);
                } else {
                    // Pago parcial: rebajar solo el monto_recibo
                    $montoRebajar = $this->monto_recibo;
                    
                    // Calcular proporción para actualizar saldos
                    $proporcion = $this->monto_recibo / $planPago->monto_total;
                    $principalPagado = $planPago->saldo_principal * $proporcion;
                    $interesPagado = $planPago->saldo_interes * $proporcion;
                    
                    $planPago->update([
                        'saldo_principal' => max(0, $planPago->saldo_principal - $principalPagado),
                        'saldo_interes' => max(0, $planPago->saldo_interes - $interesPagado),
                        'plp_estados' => (($planPago->saldo_principal - $principalPagado) <= 0 && 
                                         ($planPago->saldo_interes - $interesPagado) <= 0)
                            ? 'completado' 
                            : 'pendiente',
                        'fecha_pago_real' => $this->fecha_pago
                    ]);
                }
    
                $totalRebajar += $montoRebajar;
            }
    
            // Actualizar saldo del préstamo (solo se rebaja el principal pagado)
            // Asumiendo que monto_total incluye principal + interés
            // Calculamos la proporción del principal en el pago
            $proporcionPrincipal = $this->prestamo->saldo_prestamo / 
                                 ($this->prestamo->saldo_prestamo + $this->prestamo->total_intereses_pendientes);
            
            $principalRebajado = $totalRebajar * $proporcionPrincipal;
            
            $this->prestamo->update([
                'saldo_prestamo' => max(0, $this->prestamo->saldo_prestamo - $principalRebajado),
                'proximo_pago' => $this->prestamo->planpagos()
                    ->where('plp_estados', 'pendiente')
                    ->orderBy('fecha_pago')
                    ->first()?->fecha_pago
            ]);
    
            // Marcar recibo como completado
            $this->update(['estado' => 'C']);
        });
    }

public function anular()
{
    DB::transaction(function () {
        // Validar estados permitidos: Incluido (I) o Completado (C)
        if (!in_array($this->estado, ['I', 'C'])) {
            throw new \Exception('Solo se pueden anular recibos en estado Incluido o Completado');
        }

        // Solo revertir saldos si el recibo estaba Completado (C)
        if ($this->estado === 'C') {
            foreach ($this->detalles as $detalle) {
                $planPago = $detalle->planpago;
                
                // Calcular los montos a revertir correctamente
                $montoPrincipalRevertir = $detalle->monto_principal_original ?? $detalle->monto_principal;
                $montoInteresesRevertir = $detalle->monto_intereses_original ?? $detalle->monto_intereses;
                
                // Para pagos parciales, solo revertir el monto efectivamente pagado
                if ($this->tipo_pago === 'parcial') {
                    $montoPrincipalRevertir = min($montoPrincipalRevertir, $detalle->monto_principal);
                    $montoInteresesRevertir = min($montoInteresesRevertir, $detalle->monto_intereses);
                }

                $planPago->update([
                    'saldo_principal' => $planPago->saldo_principal + $montoPrincipalRevertir,
                    'saldo_interes' => $planPago->saldo_interes + $montoInteresesRevertir,
                    'plp_estados' => 'pendiente'
                ]);
            }

            // Restaurar saldo del préstamo solo con el principal revertido
            $totalPrincipalRevertido = $this->detalles->sum(function ($detalle) {
                $montoPrincipalRevertir = $detalle->monto_principal_original ?? $detalle->monto_principal;
                return $this->tipo_pago === 'parcial' 
                    ? min($montoPrincipalRevertir, $detalle->monto_principal)
                    : $montoPrincipalRevertir;
            });

            $this->prestamo->update([
                'saldo_prestamo' => $this->prestamo->saldo_prestamo + $totalPrincipalRevertido,
                'proximo_pago' => $this->prestamo->planpagos()
                    ->where('plp_estados', 'pendiente')
                    ->orderBy('fecha_pago')
                    ->first()?->fecha_pago
            ]);
        }

        // Eliminar detalles y recibo
        $this->detalles()->delete();
        $this->delete();
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
