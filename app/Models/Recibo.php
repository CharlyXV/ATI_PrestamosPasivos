<?php

// app/Models/Recibo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Support\Facades\DB;
use App\Models\Pago; // Añade esta línea si no está
use App\Models\Planpago; // Añade esta línea si no está
use App\Models\Prestamo; // Añade esta línea si no está

class Recibo extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_recibo', 'tipo_recibo', 'detalle', 'estado',
        'cuenta_id', 'monto_recibo', 'fecha_pago', 'fecha_deposito', 'prestamo_id'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'fecha_deposito' => 'date',
        'monto_recibo' => 'decimal:2'
    ];

    // Relación con los detalles
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleRecibo::class);
    }

    // Relación con el préstamo
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    // Relación con la cuenta bancaria
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    // Generar número de recibo automático
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->numero_recibo = $model->numero_recibo ?? 'REC-' . now()->format('YmdHis');
        });
    }

    // En app/Models/Recibo.php
public function procesarPago()
{
    DB::transaction(function () {
        // Validar que el recibo esté en estado Incluido
        if ($this->estado != 'I') {
            throw new \Exception('Solo se pueden procesar recibos en estado Incluido');
        }

        // Validar que los montos no excedan los saldos
        foreach ($this->detalles as $detalle) {
            $planpago = $detalle->planpago;
            
            if ($detalle->monto_principal > $planpago->saldo_principal ||
                $detalle->monto_intereses > $planpago->saldo_interes ||
                $detalle->monto_seguro > $planpago->saldo_seguro ||
                $detalle->monto_otros > $planpago->saldo_otros) {
                throw new \Exception('Los montos exceden los saldos disponibles en la cuota #' . $detalle->numero_cuota);
            }
        }

        // Actualizar estado del recibo
        $this->estado = 'C';
        $this->save();

        // Procesar cada detalle
        foreach ($this->detalles as $detalle) {
            $planpago = $detalle->planpago;
            
            // Actualizar saldos en plan de pagos
            $planpago->update([
                'saldo_principal' => $planpago->saldo_principal - $detalle->monto_principal,
                'saldo_interes' => $planpago->saldo_interes - $detalle->monto_intereses,
                'saldo_seguro' => $planpago->saldo_seguro - $detalle->monto_seguro,
                'saldo_otros' => $planpago->saldo_otros - $detalle->monto_otros,
                'plp_estados' => ($planpago->saldo_principal <= 0 && 
                                $planpago->saldo_interes <= 0 && 
                                $planpago->saldo_seguro <= 0 && 
                                $planpago->saldo_otros <= 0) ? 'completado' : 'pendiente'
            ]);
            
            // Registrar el pago
            Pago::create([
                'planpago_id' => $planpago->id,
                'prestamo_id' => $this->prestamo_id,
                'monto' => $detalle->monto_cuota,
                'fecha_pago' => $this->fecha_pago,
                'referencia' => $this->numero_recibo,
                'estado' => 'completado',
                'moneda' => $this->prestamo->moneda
            ]);
        }

        // Actualizar préstamo
        $prestamo = $this->prestamo;
        $prestamo->update([
            'saldo_prestamo' => $prestamo->saldo_prestamo - $this->detalles->sum('monto_principal'),
            'proximo_pago' => $prestamo->planpagos()
                ->where('plp_estados', 'pendiente')
                ->orderBy('fecha_pago')
                ->first()?->fecha_pago
        ]);
    });
}

public function anularRecibo()
{
    DB::transaction(function () {
        // Validar que el recibo no esté ya anulado
        if ($this->estado == 'A') {
            throw new \Exception('El recibo ya está anulado');
        }

        // Si estaba contabilizado, revertir los cambios
        if ($this->estado == 'C') {
            foreach ($this->detalles as $detalle) {
                $planpago = $detalle->planpago;
                
                // Revertir saldos en plan de pagos
                $planpago->update([
                    'saldo_principal' => $planpago->saldo_principal + $detalle->monto_principal,
                    'saldo_interes' => $planpago->saldo_interes + $detalle->monto_intereses,
                    'saldo_seguro' => $planpago->saldo_seguro + $detalle->monto_seguro,
                    'saldo_otros' => $planpago->saldo_otros + $detalle->monto_otros,
                    'plp_estados' => 'pendiente'
                ]);
                
                // Eliminar el pago asociado
                Pago::where('referencia', $this->numero_recibo)
                    ->where('planpago_id', $planpago->id)
                    ->delete();
            }

            // Revertir préstamo
            $prestamo = $this->prestamo;
            $prestamo->update([
                'saldo_prestamo' => $prestamo->saldo_prestamo + $this->detalles->sum('monto_principal'),
                'proximo_pago' => $prestamo->planpagos()
                    ->where('plp_estados', 'pendiente')
                    ->orderBy('fecha_pago')
                    ->first()?->fecha_pago
            ]);
        }

        // Actualizar estado del recibo
        $this->estado = 'A';
        $this->save();
    });
}

public function generarReciboPDF()
{
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('recibos.template', [
        'recibo' => $this
    ]);
    
    return $pdf->stream("recibo-{$this->numero_recibo}.pdf");
}

}




