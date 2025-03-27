<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = [
        'prestamo_id',
        'planpago_id',
        'monto',
        'moneda', // Añadido para almacenar la moneda
        'fecha_pago',
        'referencia',
        'estado',
    ];

    protected $casts = [
        'monto' => 'decimal:2', // Asegura el formato decimal correcto
        'fecha_pago' => 'date',
    ];

 // En app/Models/Pago.php
public function prestamo(): BelongsTo
{
    return $this->belongsTo(Prestamo::class)->withDefault([
        'moneda' => 'CRC',
        'numero_prestamo' => 'N/A'
    ]);
}

public function planpago(): BelongsTo
{
    return $this->belongsTo(Planpago::class)->withDefault([
        'numero_cuota' => 0,
        'monto_total' => 0
    ]);
}

    /**
     * Relación indirecta para acceder al préstamo a través del plan de pago
     */
    public function prestamoThroughPlanpago()
    {
        return $this->hasOneThrough(
            Prestamo::class,
            Planpago::class,
            'id', // Foreign key en Planpago
            'id', // Foreign key en Prestamo
            'planpago_id', // Local key en Pago
            'prestamo_id' // Local key en Planpago
        );
    }
}