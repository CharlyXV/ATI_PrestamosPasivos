<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Planpago extends Model
{

    use HasFactory;
    
  // app/Models/Planpago.php
  protected $casts = [
    'saldo_prestamo' => 'decimal:2',
    'saldo_principal' => 'decimal:2',
    'saldo_interes' => 'decimal:2',
    'saldo_seguro' => 'decimal:2',
    'saldo_otros' => 'decimal:2',
    'monto_principal' => 'decimal:2',
    'monto_interes' => 'decimal:2',
    'monto_seguro' => 'decimal:2',
    'monto_otros' => 'decimal:2',
    'tasa_interes' => 'decimal:2'
];
protected $fillable = [
        'prestamo_id',
        'numero_cuota',
        'fecha_pago',
        'monto_principal',
        'monto_interes',
        'monto_seguro',
        'monto_otros',
        'saldo_prestamo',
        'tasa_interes',
        'saldo_principal',
        'saldo_interes',
        'saldo_seguro',
        'saldo_otros',
        'observaciones',
    ];
    protected $attributes = [
        'saldo_prestamo' => 0,
        'saldo_principal' => 0,
        'saldo_interes' => 0,
        'saldo_seguro' => 0,
        'saldo_otros' => 0,
        'tasa_interes' => 0,
        'plp_estados' => 'pendiente',
        'observaciones' => 'Pago programado'
    ];   
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    /**
     * Relación con Pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }



}
