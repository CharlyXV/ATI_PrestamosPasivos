<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = [
        'prestamo_id',
        'planpago_id',
        'monto', // Asegúrate de que esta columna esté en el array
        'fecha_pago',
        'referencia',
        'estado',
    ];

    // Relación con Prestamo
    public function prestamos(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class, 'prestamo_id');
    }

    // Relación con Planpago
    public function planpagos(): BelongsTo
    {
        return $this->belongsTo(Planpago::class, 'planpago_id');
    }
}