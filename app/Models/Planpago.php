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
    'fecha_pago' => 'date:Y-m-d',
    // ... otros casts
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
